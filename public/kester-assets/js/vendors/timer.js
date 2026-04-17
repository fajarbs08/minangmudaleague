const daysEl = document.getElementById("days");
const hoursEl = document.getElementById("hours");
const minsEl = document.getElementById("mins");
const secondsEl = document.getElementById("seconds");

if (!daysEl || !hoursEl || !minsEl || !secondsEl) {
    // Countdown widget not present on this page.
} else {
    const countdownRoot =
        document.querySelector("[data-countdown-ts]") ||
        document.querySelector("[data-countdown-date]") ||
        null;

    const defaultTarget = "10 jul 2026";

    function getTargetMs() {
        if (!countdownRoot) {
            return Date.parse(defaultTarget);
        }

        const tsAttr = countdownRoot.getAttribute("data-countdown-ts");
        if (tsAttr) {
            const ts = parseInt(tsAttr, 10);
            if (!Number.isNaN(ts)) {
                return ts;
            }
        }

        const dateAttr = countdownRoot.getAttribute("data-countdown-date");
        if (dateAttr) {
            const parsed = Date.parse(dateAttr);
            if (!Number.isNaN(parsed)) {
                return parsed;
            }
        }

        return Date.parse(defaultTarget);
    }

    const targetMs = getTargetMs();

    function countdown() {
        const totalSecondsRaw = (targetMs - Date.now()) / 1000;
        const totalSeconds = Math.max(0, totalSecondsRaw);

        const days = Math.floor(totalSeconds / 3600 / 24);
        const hours = Math.floor(totalSeconds / 3600) % 24;
        const mins = Math.floor(totalSeconds / 60) % 60;
        const seconds = Math.floor(totalSeconds) % 60;

        daysEl.innerHTML = String(days);
        hoursEl.innerHTML = formatTime(hours);
        minsEl.innerHTML = formatTime(mins);
        secondsEl.innerHTML = formatTime(seconds);
    }

    function formatTime(time) {
        return time < 10 ? `0${time}` : String(time);
    }

    countdown();
    setInterval(countdown, 1000);
}
