$(document).ready(function() {
    var menuIsOpen= false
    $("#MenuBtn").click(function () {
        if (!menuIsOpen) {
            menuIsOpen = true
            $("#MainMenu").addClass("open")
        } else {
            menuIsOpen = false
            $("#MainMenu").removeClass("open")
        }
    });
});