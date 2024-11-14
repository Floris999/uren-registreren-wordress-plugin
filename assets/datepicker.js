jQuery(document).ready(function ($) {
  let selectedDate = new Date();

  $("#weekpicker").datepicker({
    showWeek: true,
    firstDay: 1,
    dateFormat: "yy-mm-dd",
    onSelect: function (dateText, inst) {
      selectedDate = $(this).datepicker("getDate");
      displayWeekInfo(selectedDate);
    },
    beforeShowDay: function (date) {
      if (selectedDate) {
        const startOfWeek = getStartOfWeek(selectedDate);
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        if (date >= startOfWeek && date <= endOfWeek) {
          return [true, "ui-state-active", ""];
        }
      }
      return [true, "", ""];
    },
  });

  function displayWeekInfo(date) {
    const weekNumber = $.datepicker.iso8601Week(date);
    let year = date.getFullYear();

    if (weekNumber === 1 && date.getMonth() === 11) {
      year += 1;
    } else if (weekNumber >= 52 && date.getMonth() === 0) {
      year -= 1;
    }

    const startOfWeek = getStartOfWeek(date);
    const endOfWeek = new Date(startOfWeek);
    endOfWeek.setDate(startOfWeek.getDate() + 6);

    const startFormatted = $.datepicker.formatDate("dd-mm-yy", startOfWeek);
    const endFormatted = $.datepicker.formatDate("dd-mm-yy", endOfWeek);
    const weekDisplay = `Week ${weekNumber} (${startFormatted} tot ${endFormatted})`;

    $("#weekpicker").val(weekDisplay);
    $("#weekNumber").val(weekNumber);
    $("#year").val(year);
  }

  function getStartOfWeek(date) {
    const day = date.getDay();
    const diff = date.getDate() - day + (day === 0 ? -6 : 1); // adjust when day is sunday
    return new Date(date.setDate(diff));
  }
});