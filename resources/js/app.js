const countdownTimers = new WeakMap();

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

document.addEventListener('DOMContentLoaded', initializeCountdowns);
document.addEventListener('livewire:navigated', initializeCountdowns);
