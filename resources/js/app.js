import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import * as echarts from 'echarts';

const countdownTimers = new WeakMap();
const leafletMaps = new WeakMap();
const publicHeaderStates = new WeakMap();
const landingVideoObservers = new WeakMap();
const echartsInstances = new WeakMap();
const radarTooltipStates = new WeakMap();
const cityAutocompleteStates = new WeakMap();
const richTextEditorStates = new WeakMap();
const richTextSelections = new Map();

function updateCountdown(countdown) {
    const targetDate = new Date(countdown.dataset.countdownTarget);
    const remainingMilliseconds = Math.max(targetDate.getTime() - Date.now(), 0);
    const totalSeconds = Math.floor(remainingMilliseconds / 1000);

    const values = {
        days: Math.floor(totalSeconds / 86400),
        hours: Math.floor((totalSeconds % 86400) / 3600),
        minutes: Math.floor((totalSeconds % 3600) / 60),
        seconds: totalSeconds % 60,
    };

    countdown.querySelectorAll('[data-countdown-unit]').forEach((element) => {
        const unit = element.dataset.countdownUnit;
        const value = values[unit] ?? 0;

        element.textContent = unit === 'days' ? String(value) : String(value).padStart(2, '0');
    });
}

function initializeCountdowns() {
    document.querySelectorAll('[data-countdown-target]').forEach((countdown) => {
        if (countdownTimers.has(countdown)) {
            return;
        }

        updateCountdown(countdown);

        const timer = window.setInterval(() => updateCountdown(countdown), 1000);
        countdownTimers.set(countdown, timer);
    });
}

function markerIcon(city) {
    const size = city.selected ? 36 : 30;
    const background = city.selected ? '#173f25' : '#ffffff';
    const color = city.selected ? '#ffffff' : '#173f25';
    const border = city.selected ? '#c5a059' : '#5f7f63';

    return L.divIcon({
        className: '',
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2],
        popupAnchor: [0, -size / 2],
        html: `
            <div class="ktn-leaflet-marker" style="width:${size}px;height:${size}px;background:${background};color:${color};border-color:${border};">
                ${city.count}
            </div>
        `,
    });
}

function componentFor(element) {
    const componentRoot = element.closest('[wire\\:id]');

    if (!componentRoot || !window.Livewire) {
        return null;
    }

    return window.Livewire.find(componentRoot.getAttribute('wire:id'));
}

function locationLabel(location) {
    return [location.name, location.state_name, location.country_name].filter(Boolean).join(', ');
}

function setCityAutocompleteValue(element, field, value) {
    const input = element.querySelector(`[data-city-autocomplete-value="${field}"]`);

    if (!input) {
        return;
    }

    input.value = value ?? '';
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

function clearCityAutocompleteSelection(element, state) {
    state.selectedLabel = null;

    ['city', 'country', 'latitude', 'longitude'].forEach((field) => {
        setCityAutocompleteValue(element, field, '');
    });
}

function renderCityAutocompleteResults(element, state, locations) {
    const results = element.querySelector('[data-city-autocomplete-results]');

    results.replaceChildren();

    locations.forEach((location) => {
        const button = document.createElement('button');

        button.type = 'button';
        button.className = 'block w-full px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 focus:bg-zinc-100 focus:outline-none';
        button.textContent = locationLabel(location);
        button.addEventListener('mousedown', (event) => {
            event.preventDefault();

            const label = locationLabel(location);
            const input = element.querySelector('[data-city-autocomplete-input]');
            const status = element.querySelector('[data-city-autocomplete-status]');

            state.selectedLabel = label;
            input.value = label;
            setCityAutocompleteValue(element, 'search', label);
            setCityAutocompleteValue(element, 'city', location.name);
            setCityAutocompleteValue(element, 'country', location.country_name);
            setCityAutocompleteValue(element, 'latitude', location.latitude);
            setCityAutocompleteValue(element, 'longitude', location.longitude);
            status.textContent = 'Kota dipilih dari hasil pencarian.';
            results.classList.add('hidden');
        });

        results.append(button);
    });

    results.classList.toggle('hidden', locations.length === 0);
}

function initializeCityAutocompletes() {
    document.querySelectorAll('[data-city-autocomplete]').forEach((element) => {
        if (cityAutocompleteStates.has(element)) {
            return;
        }

        const input = element.querySelector('[data-city-autocomplete-input]');
        const results = element.querySelector('[data-city-autocomplete-results]');
        const status = element.querySelector('[data-city-autocomplete-status]');
        const state = {
            abortController: null,
            debounceTimer: null,
            selectedLabel: input.value.trim() || null,
        };

        const search = async () => {
            const query = input.value.trim();

            if (query === '') {
                status.textContent = 'Pilih kota dari daftar hasil pencarian.';
                results.classList.add('hidden');

                return;
            }

            state.abortController?.abort();
            state.abortController = new AbortController();
            status.textContent = 'Mencari kota...';

            try {
                const response = await fetch(`${element.dataset.searchUrl}/${encodeURIComponent(query)}`, {
                    headers: { Accept: 'application/json' },
                    signal: state.abortController.signal,
                });

                if (!response.ok) {
                    throw new Error(`City search failed with status ${response.status}`);
                }

                const locations = await response.json();

                renderCityAutocompleteResults(element, state, Array.isArray(locations) ? locations : []);
                status.textContent = locations.length > 0
                    ? `${locations.length} kota ditemukan. Pilih salah satu.`
                    : 'Kota tidak ditemukan.';
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                results.classList.add('hidden');
                status.textContent = 'Pencarian kota gagal. Silakan coba lagi.';
            }
        };

        input.addEventListener('input', () => {
            setCityAutocompleteValue(element, 'search', input.value.trim());

            if (input.value.trim() !== state.selectedLabel) {
                clearCityAutocompleteSelection(element, state);
            }

            window.clearTimeout(state.debounceTimer);
            state.debounceTimer = window.setTimeout(search, 250);
        });

        input.addEventListener('focus', () => {
            if (results.childElementCount > 0) {
                results.classList.remove('hidden');
            }
        });

        input.addEventListener('blur', () => {
            window.setTimeout(() => results.classList.add('hidden'), 150);
        });

        cityAutocompleteStates.set(element, state);
    });
}

function initializeDistributionMaps() {
    document.querySelectorAll('[data-leaflet-distribution-map]').forEach((element) => {
        const markers = JSON.parse(element.dataset.markers || '[]');

        if (leafletMaps.has(element)) {
            const existingMap = leafletMaps.get(element);
            window.setTimeout(() => existingMap.invalidateSize(), 50);

            return;
        }

        const selectedMarker = markers.find((marker) => marker.selected);
        const firstMarker = markers[0];
        const center = selectedMarker || firstMarker || { latitude: -2.5, longitude: 118 };
        const zoom = selectedMarker ? 7 : 4;
        const map = L.map(element, {
            scrollWheelZoom: false,
        }).setView([center.latitude, center.longitude], zoom);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        markers.forEach((city) => {
            L.marker([city.latitude, city.longitude], { icon: markerIcon(city) })
                .bindPopup(`<strong>${city.name}</strong><br>${city.country}<br>${city.count} alumni`)
                .on('click', () => {
                    const component = componentFor(element);

                    if (component) {
                        component.call('selectCity', city.id);
                    }
                })
                .addTo(map);
        });

        if (markers.length > 1) {
            const bounds = L.latLngBounds(markers.map((city) => [city.latitude, city.longitude]));
            map.fitBounds(bounds.pad(0.2), { maxZoom: 8 });
        }

        leafletMaps.set(element, map);
        window.setTimeout(() => map.invalidateSize(), 50);
    });
}

function setPublicHeaderActive(header, activeKey) {
    const links = header.querySelectorAll('[data-public-header-link]');

    links.forEach((link) => {
        const isActive = link.dataset.publicHeaderKey === activeKey;

        link.classList.toggle('text-ktn-forest', isActive);
        link.classList.toggle('underline', isActive);
        link.classList.toggle('decoration-2', isActive);
        link.classList.toggle('underline-offset-8', isActive);
        link.classList.toggle('text-ktn-muted', !isActive);

        if (isActive) {
            link.setAttribute('aria-current', 'location');
        } else {
            link.removeAttribute('aria-current');
        }
    });
}

function updatePublicHeaderFromScroll(header, state) {
    if (state.sections.length === 0) {
        return;
    }

    const offset = header.getBoundingClientRect().bottom + 24;
    const currentHash = window.location.hash.slice(1);

    if (currentHash) {
        const hashedSection = state.sections.find((entry) => entry.section.id === currentHash);

        if (hashedSection) {
            setPublicHeaderActive(header, hashedSection.key);
            state.activeKey = hashedSection.key;

            return;
        }
    }

    let activeEntry = state.sections[0];

    for (const entry of state.sections) {
        const rect = entry.section.getBoundingClientRect();

        if (rect.top <= offset) {
            activeEntry = entry;
        }
    }

    if (state.activeKey !== activeEntry.key) {
        state.activeKey = activeEntry.key;
        setPublicHeaderActive(header, activeEntry.key);
    }
}

function initializePublicHeaderNavigation() {
    document.querySelectorAll('[data-public-header]').forEach((header) => {
        if (publicHeaderStates.has(header)) {
            return;
        }

        const links = Array.from(header.querySelectorAll('[data-public-header-link]'));
        const sections = links
            .map((link) => {
                const url = new URL(link.href, window.location.href);
                const sectionId = url.hash.replace('#', '');
                const section = sectionId ? document.getElementById(sectionId) : null;

                if (!section) {
                    return null;
                }

                return {
                    key: link.dataset.publicHeaderKey,
                    section,
                };
            })
            .filter((entry) => entry !== null);

        if (sections.length === 0) {
            return;
        }

        const state = {
            activeKey: null,
            sections,
            rafId: null,
        };

        const scheduleUpdate = () => {
            if (state.rafId !== null) {
                return;
            }

            state.rafId = window.requestAnimationFrame(() => {
                state.rafId = null;
                updatePublicHeaderFromScroll(header, state);
            });
        };

        links.forEach((link) => {
            link.addEventListener('click', () => {
                window.setTimeout(scheduleUpdate, 0);
            });
        });

        window.addEventListener('scroll', scheduleUpdate, { passive: true });
        window.addEventListener('resize', scheduleUpdate);
        window.addEventListener('hashchange', scheduleUpdate);

        publicHeaderStates.set(header, state);
        scheduleUpdate();
    });
}

function initializeLandingVideos() {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    document.querySelectorAll('[data-landing-video]').forEach((video) => {
        if (landingVideoObservers.has(video)) {
            return;
        }

        if (reducedMotion.matches) {
            video.pause();

            return;
        }

        const state = {
            isIntersecting: false,
            observer: null,
        };

        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    state.isIntersecting = entry.isIntersecting;

                    if (entry.isIntersecting && document.visibilityState === 'visible') {
                        video.play().catch(() => {});
                    } else {
                        video.pause();
                    }
                });
            },
            { threshold: 0.25 },
        );

        state.observer = observer;
        observer.observe(video);
        landingVideoObservers.set(video, state);
    });
}

function updateLandingVideoVisibility() {
    document.querySelectorAll('[data-landing-video]').forEach((video) => {
        const state = landingVideoObservers.get(video);

        if (!state || document.visibilityState !== 'visible' || !state.isIntersecting) {
            video.pause();

            return;
        }

        video.play().catch(() => {});
    });
}

function initializeEcharts() {
    document.querySelectorAll('[data-echarts]').forEach((element) => {
        const option = JSON.parse(element.dataset.echartsOption || '{}');

        if (!option || typeof option !== 'object') {
            return;
        }

        const radarTooltip = prepareRadarTooltip(option);
        applyHeatmapTooltipFormatter(option);
        applyPersonalStackedBarTooltipFormatter(option);
        applyTopBarTooltipFormatter(option);

        if (!echartsInstances.has(element)) {
            const chart = echarts.init(element);

            chart.setOption(option);
            syncRadarTooltip(element, option, radarTooltip);
            window.setTimeout(() => chart.resize(), 50);
            window.setTimeout(() => chart.resize(), 250);

            const resize = () => chart.resize();
            window.addEventListener('resize', resize);
            echartsInstances.set(element, { chart, resize });

            return;
        }

        const existing = echartsInstances.get(element);
        existing.chart.setOption(option, true);
        syncRadarTooltip(element, option, radarTooltip);
        window.setTimeout(() => existing.chart.resize(), 50);
        window.setTimeout(() => existing.chart.resize(), 250);
    });
}

function initializeEchartsAfterRender() {
    window.queueMicrotask(() => {
        initializeEcharts();
        window.setTimeout(initializeEcharts, 50);
        window.setTimeout(initializeEcharts, 250);
    });
}

function prepareRadarTooltip(option) {
    if (!Array.isArray(option.radarTooltipLabels)) {
        return null;
    }

    const labels = option.radarTooltipLabels.slice();
    const series = Array.isArray(option.series)
        ? option.series
            .map((item) => ({
                name: item?.name ?? item?.data?.[0]?.name ?? 'Aktivitas',
                values: item?.data?.[0]?.value,
            }))
            .filter((item) => Array.isArray(item.values))
        : [];

    option.tooltip = {
        ...(option.tooltip || {}),
        show: false,
    };

    delete option.radarTooltipLabels;

    return series.length > 0 ? { labels, series } : null;
}

function applyHeatmapTooltipFormatter(option) {
    if (!option.heatmapTooltip || !Array.isArray(option.heatmapTooltip.hours) || !Array.isArray(option.heatmapTooltip.days)) {
        return;
    }

    const { hours, days } = option.heatmapTooltip;
    const numberFormatter = new Intl.NumberFormat('id-ID');

    option.tooltip = {
        ...(option.tooltip || {}),
        formatter(params) {
            const value = Array.isArray(params.value) ? params.value : [];
            const hour = hours[value[0]] ?? '-';
            const day = days[value[1]] ?? '-';
            const total = numberFormatter.format(Number(value[2]) || 0);

            return `${day}, ${hour}<br><strong>${total}</strong> aktivitas`;
        },
    };

    delete option.heatmapTooltip;
}

function applyTopBarTooltipFormatter(option) {
    if (option.topBarTooltip !== true) {
        return;
    }

    const numberFormatter = new Intl.NumberFormat('id-ID');

    option.tooltip = {
        ...(option.tooltip || {}),
        formatter(params) {
            const item = Array.isArray(params) ? params[0] : params;
            const name = item?.name ?? '-';
            const value = numberFormatter.format(Number(item?.value) || 0);

            return `${name}<br><strong>${value}</strong> aktivitas`;
        },
    };

    delete option.topBarTooltip;
}

function applyPersonalStackedBarTooltipFormatter(option) {
    if (option.personalStackedBarTooltip !== true) {
        return;
    }

    const numberFormatter = new Intl.NumberFormat('id-ID');

    option.tooltip = {
        ...(option.tooltip || {}),
        trigger: 'item',
        formatter(params) {
            const value = numberFormatter.format(Number(params?.value) || 0);
            const marker = params?.marker ?? '';
            const name = params?.name ?? '-';
            const seriesName = params?.seriesName ?? '-';

            return `${name}<br>${marker} ${seriesName} <strong>${value}</strong> aktivitas`;
        },
    };

    delete option.personalStackedBarTooltip;
}

function syncRadarTooltip(element, option, tooltipData) {
    const existing = radarTooltipStates.get(element);

    if (existing) {
        element.removeEventListener('mousemove', existing.onMove);
        element.removeEventListener('mouseleave', existing.onLeave);
        existing.tooltip.remove();
        radarTooltipStates.delete(element);
    }

    if (!tooltipData || !Array.isArray(option.radar?.indicator)) {
        return;
    }

    element.style.position = element.style.position || 'relative';

    const tooltip = document.createElement('div');
    tooltip.className = 'pointer-events-none absolute z-10 hidden rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm shadow-lg dark:border-zinc-700 dark:bg-zinc-900';
    element.append(tooltip);

    const onMove = (event) => {
        const nearest = nearestRadarPoint(event, element, option, tooltipData);

        if (!nearest) {
            tooltip.classList.add('hidden');

            return;
        }

        tooltip.innerHTML = `${nearest.series}<br>${nearest.label}<br><strong>${nearest.value}</strong> aktivitas`;
        tooltip.style.left = `${Math.min(nearest.cursorX + 12, element.clientWidth - tooltip.offsetWidth - 8)}px`;
        tooltip.style.top = `${Math.max(nearest.cursorY - tooltip.offsetHeight - 12, 8)}px`;
        tooltip.classList.remove('hidden');
    };

    const onLeave = () => tooltip.classList.add('hidden');

    element.addEventListener('mousemove', onMove);
    element.addEventListener('mouseleave', onLeave);
    radarTooltipStates.set(element, { onMove, onLeave, tooltip });
}

function nearestRadarPoint(event, element, option, tooltipData) {
    const rect = element.getBoundingClientRect();
    const cursorX = event.clientX - rect.left;
    const cursorY = event.clientY - rect.top;
    const centerX = rect.width / 2;
    const centerY = rect.height / 2;
    const radius = Math.min(rect.width, rect.height) * 0.34;
    const indicators = option.radar.indicator;
    const max = Math.max(...indicators.map((indicator) => Number(indicator.max) || 1), 1);
    const startAngle = Number(option.radar.startAngle ?? 90);
    const pointCount = tooltipData.series[0]?.values?.length ?? 0;
    const step = pointCount > 0 ? 360 / pointCount : 0;

    let nearest = null;

    tooltipData.series.forEach((series) => {
        series.values.forEach((rawValue, index) => {
            const value = Number(rawValue) || 0;
            const angle = ((startAngle + (index * step)) * Math.PI) / 180;
            const distance = radius * Math.min(value / max, 1);
            const pointX = centerX + Math.cos(angle) * distance;
            const pointY = centerY - Math.sin(angle) * distance;
            const pointDistance = Math.hypot(cursorX - pointX, cursorY - pointY);

            if (!nearest || pointDistance < nearest.distance) {
                nearest = {
                    distance: pointDistance,
                    series: series.name,
                    label: tooltipData.labels[index],
                    value,
                    cursorX,
                    cursorY,
                };
            }
        });
    });

    return nearest && nearest.distance <= 26 ? nearest : null;
}

function updateRichTextValue(textarea, value, selectionStart, selectionEnd = selectionStart) {
    textarea.value = value;
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
    textarea.focus();
    textarea.setSelectionRange(selectionStart, selectionEnd);
}

function wrapRichTextSelection(textarea, prefix, suffix, placeholder) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.slice(start, end) || placeholder;
    const replacement = `${prefix}${selectedText}${suffix}`;
    const value = `${textarea.value.slice(0, start)}${replacement}${textarea.value.slice(end)}`;
    const selectionStart = start + prefix.length;

    updateRichTextValue(textarea, value, selectionStart, selectionStart + selectedText.length);
}

function prefixRichTextLines(textarea, prefixForIndex) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const lineStart = textarea.value.lastIndexOf('\n', Math.max(start - 1, 0)) + 1;
    const nextLineBreak = textarea.value.indexOf('\n', end);
    const lineEnd = nextLineBreak === -1 ? textarea.value.length : nextLineBreak;
    const selectedLines = textarea.value.slice(lineStart, lineEnd).split('\n');
    const replacement = selectedLines
        .map((line, index) => `${prefixForIndex(index)}${line}`)
        .join('\n');
    const value = `${textarea.value.slice(0, lineStart)}${replacement}${textarea.value.slice(lineEnd)}`;

    updateRichTextValue(textarea, value, lineStart, lineStart + replacement.length);
}

function applyRichTextAction(textarea, action) {
    const actions = {
        heading: () => prefixRichTextLines(textarea, () => '## '),
        bold: () => wrapRichTextSelection(textarea, '**', '**', 'teks tebal'),
        italic: () => wrapRichTextSelection(textarea, '_', '_', 'teks miring'),
        'bullet-list': () => prefixRichTextLines(textarea, () => '- '),
        'numbered-list': () => prefixRichTextLines(textarea, (index) => `${index + 1}. `),
        quote: () => prefixRichTextLines(textarea, () => '> '),
        link: () => wrapRichTextSelection(textarea, '[', '](https://)', 'teks tautan'),
    };

    actions[action]?.();
}

function rememberRichTextSelection(editor, textarea) {
    richTextSelections.set(editor.dataset.richTextEditorId, {
        start: textarea.selectionStart,
        end: textarea.selectionEnd,
    });
}

function initializeRichTextEditors() {
    document.querySelectorAll('[data-rich-text-editor]').forEach((editor) => {
        if (richTextEditorStates.has(editor)) {
            return;
        }

        const textarea = editor.querySelector('[data-rich-text-input]');

        if (!textarea) {
            return;
        }

        const handleAction = (event) => {
            const button = event.target.closest('[data-rich-text-action]');

            if (!button || !editor.contains(button)) {
                return;
            }

            event.preventDefault();

            if (button.dataset.richTextAction === 'image') {
                rememberRichTextSelection(editor, textarea);
                editor.querySelector('[data-rich-text-image-input]')?.click();

                return;
            }

            applyRichTextAction(textarea, button.dataset.richTextAction);
            rememberRichTextSelection(editor, textarea);
        };

        const rememberSelection = () => rememberRichTextSelection(editor, textarea);

        editor.addEventListener('click', handleAction);
        textarea.addEventListener('click', rememberSelection);
        textarea.addEventListener('keyup', rememberSelection);
        textarea.addEventListener('select', rememberSelection);
        textarea.addEventListener('input', rememberSelection);
        rememberSelection();
        richTextEditorStates.set(editor, { handleAction, rememberSelection });
    });
}

document.addEventListener('news-content-image-uploaded', (event) => {
    window.queueMicrotask(() => {
        initializeRichTextEditors();

        const editor = document.querySelector(`[data-rich-text-editor-id="${CSS.escape(event.detail.editorId)}"]`);
        const textarea = editor?.querySelector('[data-rich-text-input]');

        if (!editor || !textarea) {
            return;
        }

        const selection = richTextSelections.get(event.detail.editorId) ?? {
            start: textarea.value.length,
            end: textarea.value.length,
        };
        const prefix = selection.start > 0 && textarea.value[selection.start - 1] !== '\n' ? '\n\n' : '';
        const suffix = selection.end < textarea.value.length && textarea.value[selection.end] !== '\n' ? '\n\n' : '';
        const markdown = `${prefix}${event.detail.markdown}${suffix}`;
        const value = `${textarea.value.slice(0, selection.start)}${markdown}${textarea.value.slice(selection.end)}`;
        const cursor = selection.start + markdown.length;

        updateRichTextValue(textarea, value, cursor);
        rememberRichTextSelection(editor, textarea);
    });
});

document.addEventListener('DOMContentLoaded', initializeCountdowns);
document.addEventListener('DOMContentLoaded', initializeDistributionMaps);
document.addEventListener('DOMContentLoaded', initializePublicHeaderNavigation);
document.addEventListener('DOMContentLoaded', initializeLandingVideos);
document.addEventListener('DOMContentLoaded', initializeEcharts);
document.addEventListener('DOMContentLoaded', initializeCityAutocompletes);
document.addEventListener('DOMContentLoaded', initializeRichTextEditors);
document.addEventListener('livewire:init', () => {
    window.Livewire.interceptMessage(({ onSuccess }) => {
        onSuccess(() => window.queueMicrotask(() => {
            initializeCityAutocompletes();
            initializeRichTextEditors();
            initializeEchartsAfterRender();
        }));
    });
});
document.addEventListener('visibilitychange', updateLandingVideoVisibility);
document.addEventListener('livewire:navigated', () => {
    initializeCountdowns();
    initializeDistributionMaps();
    initializePublicHeaderNavigation();
    initializeLandingVideos();
    initializeEcharts();
    initializeCityAutocompletes();
    initializeRichTextEditors();
});
