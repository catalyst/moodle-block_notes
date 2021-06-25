define(['jquery', 'core/ajax', 'core/templates', 'core/modal_factory', 'core/modal_events'],
    function($, ajax, templates, ModalFactory, ModalEvents) {

        var saveDataToServer = function(ctxid, blockid, userid, imagedata, labelid, notedescription)
        {
            let datestr = Date.now();
            var promises = ajax.call([{
                methodname: 'block_notes_upload',
                args: {
                    contextid: ctxid,
                    component: 'user',
                    filearea: 'draft', // TODO: set proper area
                    itemid: blockid,
                    filepath: '/',
                    filename: 'note-screen-' + Date.now() + '.png',
                    userid: userid,
                    filecontent: imagedata,
                    contextlevel: 'block',
                    instanceid: blockid,
                    labelid: labelid,
                    noteurl: window.location.href,
                    notedescription: notedescription
                }
            }], true);
            $.when.apply($, promises)
                .done(function(data) {
                    alert('Note is saved');
                })
                .fail(function(data) {
                    console.log(data);
                });

        };

        var doModalDialog = function(ctxid, blockid, userid, courseid, imagedata) {
            let labelid = 14;
            let notedescription = 'Another Note';
            ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: 'Save note',
                body: 'Do you really want to save?',
            })
                .then(function (modal) {
                    var root = modal.getRoot();
                    root.on(ModalEvents.save, function () {
                        saveDataToServer(ctxid, blockid, userid, imagedata, labelid, notedescription)
                    });
                    modal.show();
                });
        };

    return {
        initNote: function() {
        },
        cancel: function() {
            document.getElementById('note_display_over_block').style.display = "none";
            document.getElementById('make_note_button').style.display = "block";
        },
        showCropTool: function() {
            document.getElementById('note_display_over_block').style.display = "block";
            document.getElementById('make_note_button').style.display = "none";
        },
        makeScreenshot: function(crop_elem, ctxid, blockid, userid, courseid) {
            const screenshotTarget = document.body;
            const element = document.querySelector(crop_elem);
            var rect = element.getBoundingClientRect();
            var blockobject = document.getElementById('note_display_over_block');
            blockobject.style.display = "none";
            // Show the wait block
            document.getElementById('note_display_wait_block').style.display = "block";
            require(['block_notes/html2canvas'], function(h2c) {
                let xx = rect.left + window.scrollX;
                let yy = rect.top + 2 *window.scrollY;
                h2c(document.body, {
                    scale: 1,
                    x: xx,
                    y: yy,
                    width : rect.width,
                    height : rect.height,
                }).then(function(canvas) {
                    let base64image = canvas.toDataURL("image/png");
                    document.getElementById('note_display_wait_block').style.display = "none";
                    doModalDialog(ctxid, blockid, userid, courseid, base64image);
                });
            });
            // Hide the wait block
            document.getElementById('make_note_button').style.display = "block";
        },
        activateCropTool: function(crop_elem) {
            const element = document.querySelector(crop_elem);
            const resizers = document.querySelectorAll(crop_elem + ' .crop-tool-control')
            const minimum_width = 180;
            const minimum_height = 80;
            let original_width = 0;
            let original_height = 0;
            let original_x = 0;
            let original_y = 0;
            let original_mouse_x = 0;
            let original_mouse_y = 0;
            for (let i = 0; i < resizers.length; i++) {
                const currentResizer = resizers[i];
                currentResizer.addEventListener('mousedown', function(e) {
                    e.preventDefault()
                    original_width = parseFloat(getComputedStyle(element, null).getPropertyValue('width').replace('px', ''));
                    original_height = parseFloat(getComputedStyle(element, null).getPropertyValue('height').replace('px', ''));
                    original_x = element.getBoundingClientRect().left;
                    original_y = element.getBoundingClientRect().top;
                    original_mouse_x = e.pageX;
                    original_mouse_y = e.pageY;
                    if (currentResizer.classList.contains('crop-tool-window')) {
                        let scr_x_pos = e.pageX - window.scrollX;
                        let scr_y_pos = e.pageY - window.scrollY;
                        if (scr_x_pos - original_x < 10 ||
                            scr_y_pos - original_y < 10 ||
                            original_x + original_width - scr_x_pos < 10 ||
                            original_y + original_height - scr_y_pos < 10
                        ) {
                            return;
                        }
                    }
                    window.addEventListener('mousemove', resize)
                    window.addEventListener('mouseup', stopResize)
                })

                function resize(e) {
                    if (currentResizer.classList.contains('crop-tool-window')) {
                        let pos_x = original_x + (e.pageX - original_mouse_x);
                        let pos_y = original_y + (e.pageY - original_mouse_y);
                        if (pos_x <= 0)
                            pos_x = 0;
                        if (pos_y <= 0)
                            pos_y = 0;
                        if (pos_x + original_width >= element.parentElement.getBoundingClientRect().width)
                            pos_x = element.parentElement.getBoundingClientRect().width - original_width;
                        if (pos_y + original_height >= element.parentElement.getBoundingClientRect().height)
                            pos_y = element.parentElement.getBoundingClientRect().height - original_height;

                        element.style.left = pos_x + 'px';
                        element.style.top = pos_y + 'px';
                    }
                    else if (currentResizer.classList.contains('bottom-right')) {
                        const width = original_width + (e.pageX - original_mouse_x);
                        const height = original_height + (e.pageY - original_mouse_y);
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
                    window.removeEventListener('mousemove', resize);
                    allow_element = true;
                }
            }
        }
    }
});