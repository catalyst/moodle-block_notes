define([], function() {
    return {
        initNote: function() {
            window.console.log('Block Notes initialized');
        },
        makeScreenshot: function() {
            const screenshotTarget = document.body;
            var wait_popup = document.getElementById('note_wait_pop_message');
            wait_popup.style.display = "block";
            require(['block_notes/html2canvas'], function(h2c) {
                console.log('Making screenshot');
                h2c(document.body, {
                    width : 800,
                    height : 600
                }).then(function(canvas) {
                    console.log('Got screenshot');
                    const base64image = canvas.toDataURL("image/png");
                    window.open(base64image, "_blank");
                    wait_popup.style.display = "none";
                });
            });

        }
    }
});