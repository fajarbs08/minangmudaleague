import flatpickr from "flatpickr";

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("input[data-time-picker-24h]").forEach((input) => {
    flatpickr(input, {
      enableTime: true,
      noCalendar: true,
      dateFormat: "H:i",
      time_24hr: true,
      allowInput: true,
      clickOpens: true,
    });
  });
});
