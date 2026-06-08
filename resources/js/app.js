import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const countdownTimers = new WeakMap();
const leafletMaps = new WeakMap();

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

document.addEventListener('DOMContentLoaded', initializeCountdowns);
document.addEventListener('DOMContentLoaded', initializeDistributionMaps);
document.addEventListener('livewire:navigated', () => {
    initializeCountdowns();
    initializeDistributionMaps();
});
