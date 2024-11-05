jQuery(document).ready(function ($) {
  $("#weekpicker").datepicker({
    showWeek: true,
    firstDay: 1,
    onSelect: function (dateText, inst) {
      var date = $(this).datepicker("getDate");
      var weekNumber = $.datepicker.iso8601Week(date);
      var startDate = new Date(date);
      startDate.setDate(startDate.getDate() - startDate.getDay() + 1);
      var endDate = new Date(startDate);
      endDate.setDate(endDate.getDate() + 6);
      var formattedStartDate = $.datepicker.formatDate("dd-mm-yy", startDate);
      var formattedEndDate = $.datepicker.formatDate("dd-mm-yy", endDate);
      $("#weeknummer").val(weekNumber);
      $("#weekdate").val(formattedStartDate + " t/m " + formattedEndDate);
      $(this).val(
        "Week " +
          weekNumber +
          " " +
          formattedStartDate +
          " t/m " +
          formattedEndDate
      );
    },
    beforeShowDay: function (date) {
      var cssClass = "";
      if (date.getDay() === 1) {
        cssClass = "ui-datepicker-week-start";
      }
      return [true, cssClass];
    },
  });
});
