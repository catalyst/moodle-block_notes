define([], function() {
    return {
        initNote: function() {
            window.console.log('Block Notes initialized');
        },
        cancel: function() {
            document.getElementById('note_display_over_block').style.display = "none";
            document.getElementById('make_note_button').style.display = "block";
        },
        showCropTool: function() {
            document.getElementById('note_display_over_block').style.display = "block";
            document.getElementById('make_note_button').style.display = "none";
        },
        makeScreenshot: function() {
            const screenshotTarget = document.body;
            document.getElementById('note_display_over_block').style.display = "block";
            document.getElementById('make_note_button').style.display = "none";
            console.log('Making screenshot');
            /*require(['block_notes/html2canvas'], function(h2c) {
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
            });*/

        },


        activateCropTool: function(div) {
            const element = document.querySelector(div);
            const resizers = document.querySelectorAll(div + ' .resizer')
            const minimum_width = 250;
            const minimum_height = 150;
            let original_width = 0;
            let original_height = 0;
            let original_x = 0;
            let original_y = 0;
            let original_mouse_x = 0;
            let original_mouse_y = 0;
            for (let i = 0;i < resizers.length; i++) {
                const currentResizer = resizers[i];
                currentResizer.addEventListener('mousedown', function(e) {
                    e.preventDefault()
                    original_width = parseFloat(getComputedStyle(element, null).getPropertyValue('width').replace('px', ''));
                    original_height = parseFloat(getComputedStyle(element, null).getPropertyValue('height').replace('px', ''));
                    original_x = element.getBoundingClientRect().left;
                    original_y = element.getBoundingClientRect().top;
                    original_mouse_x = e.pageX;
                    original_mouse_y = e.pageY;
                    window.addEventListener('mousemove', resize)
                    window.addEventListener('mouseup', stopResize)
                })

                function resize(e) {
                    if (currentResizer.classList.contains('bottom-right')) {
                        const width = original_width + (e.pageX - original_mouse_x);
                        const height = original_height + (e.pageY - original_mouse_y)
                        if (width > minimum_width) {
                            element.style.width = width + 'px'
                        }
                        if (height > minimum_height) {
                            element.style.height = height + 'px'
                        }
                    }
                    else if (currentResizer.classList.contains('bottom-left')) {
                        const height = original_height + (e.pageY - original_mouse_y)
                        const width = original_width - (e.pageX - original_mouse_x)
                        if (height > minimum_height) {
                            element.style.height = height + 'px'
                        }
                        if (width > minimum_width) {
                            element.style.width = width + 'px'
                            element.style.left = original_x + (e.pageX - original_mouse_x) + 'px'
                        }
                    }
                    else if (currentResizer.classList.contains('top-right')) {
                        const width = original_width + (e.pageX - original_mouse_x)
                        const height = original_height - (e.pageY - original_mouse_y)
                        if (width > minimum_width) {
                            element.style.width = width + 'px'
                        }
                        if (height > minimum_height) {
                            element.style.height = height + 'px'
                            element.style.top = original_y + (e.pageY - original_mouse_y) + 'px'
                        }
                    }
                    else {
                        const width = original_width - (e.pageX - original_mouse_x)
                        const height = original_height - (e.pageY - original_mouse_y)
                        if (width > minimum_width) {
                            element.style.width = width + 'px'
                            element.style.left = original_x + (e.pageX - original_mouse_x) + 'px'
                        }
                        if (height > minimum_height) {
                            element.style.height = height + 'px'
                            element.style.top = original_y + (e.pageY - original_mouse_y) + 'px'
                        }
                    }
                }

                function stopResize() {
                    window.removeEventListener('mousemove', resize)
                }
            }
        }


    }
});