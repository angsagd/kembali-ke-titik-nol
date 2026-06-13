import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import * as echarts from 'echarts';

const countdownTimers = new WeakMap();
const leafletMaps = new WeakMap();
const publicHeaderStates = new WeakMap();
const landingVideoObservers = new WeakMap();
const echartsInstances = new WeakMap();
const cityAutocompleteStates = new WeakMap();

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

        if (!echartsInstances.has(element)) {
            const chart = echarts.init(element);

            chart.setOption(option);

            const resize = () => chart.resize();
            window.addEventListener('resize', resize);
            echartsInstances.set(element, { chart, resize });

            return;
        }

        const existing = echartsInstances.get(element);
        existing.chart.setOption(option, true);
        window.setTimeout(() => existing.chart.resize(), 50);
    });
}

document.addEventListener('DOMContentLoaded', initializeCountdowns);
document.addEventListener('DOMContentLoaded', initializeDistributionMaps);
document.addEventListener('DOMContentLoaded', initializePublicHeaderNavigation);
document.addEventListener('DOMContentLoaded', initializeLandingVideos);
document.addEventListener('DOMContentLoaded', initializeEcharts);
document.addEventListener('DOMContentLoaded', initializeCityAutocompletes);
document.addEventListener('livewire:init', () => {
    window.Livewire.interceptMessage(({ onSuccess }) => {
        onSuccess(() => window.queueMicrotask(initializeCityAutocompletes));
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
});
