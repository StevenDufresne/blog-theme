(function () {
  const availabilityStatus = document.querySelector("#availability-status");

  if (!availabilityStatus) {
    return;
  }

  function updateAvailability() {
    const hourInSeoul = Number(new Intl.DateTimeFormat("en-US", {
      hour: "numeric",
      hour12: false,
      timeZone: "Asia/Seoul"
    }).format(new Date()));
    const isOnline = hourInSeoul >= 8 && hourInSeoul < 17;

    availabilityStatus.textContent = isOnline ? "online" : "offline";
    availabilityStatus.closest(".handle").classList.toggle("is-offline", !isOnline);
  }

  updateAvailability();
  window.setInterval(updateAvailability, 60000);
})();
