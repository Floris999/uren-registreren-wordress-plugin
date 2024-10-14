function updateWeekDates() {
    const weeknummer = document.getElementById('weeknummer').value;
    const year = new Date().getFullYear();
    if (weeknummer) {
        const startDate = getStartDateOfWeek(weeknummer, year);
        const endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 6);

        document.getElementById('week-dates').innerHTML = `Startdatum: ${formatDate(startDate)}<br> Einddatum: ${formatDate(endDate)}`;
    } else {
        document.getElementById('week-dates').innerHTML = '';
    }
}

function getStartDateOfWeek(week, year) {
    const simple = new Date(year, 0, 1 + (week - 1) * 7);
    const dow = simple.getDay();
    const ISOweekStart = simple;
    if (dow <= 4)
        ISOweekStart.setDate(simple.getDate() - simple.getDay() + 1);
    else
        ISOweekStart.setDate(simple.getDate() + 8 - simple.getDay());
    return ISOweekStart;
}

function formatDate(date) {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
}