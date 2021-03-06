var mymodal = null;
define(['jquery', 'core/ajax', 'core/templates', 'core/modal_factory', 'core/modal_events'],
    function($, ajax, Templates, ModalFactory, ModalEvents) {

        var saveDataToServer = function(ctxid, blockid, imagedata, labelid, newlabelname, notedescription)
        {
            let datestr = Date.now();
            var promises = ajax.call([{
                methodname: 'block_notes_upload',
                args: {
                    contextid: ctxid,
                    filename: 'note-screen-' + Date.now() + '.png',
                    filecontent: imagedata,
                    instanceid: blockid,
                    labelid: labelid,
                    newlabelname: newlabelname,
                    noteurl: window.location.href,
                    notedescription: notedescription
                }
            }], true);
            $.when.apply($, promises)
                .done(function(data) {
                    alert('Note is saved');
                })
                .fail(function(data) {
                    alert('Error saving the note: ' + data.message);
                    console.log(data);
                });

        };

        var doModalDialog = function(ctxid, blockid, courseid, imagedata) {
            var promises = ajax.call([{
                    methodname: 'block_notes_get_labels',
                    args: {
                        courseid: courseid
                    }
            }], true);

            $.when.apply($, promises)
                .done(function(data) {
                    if (data.length > 0)
                    {
                        data[0].sel = true;
                        var opts = {
                            options: data,
                            labels: true
                        };
                    }
                    if (mymodal != null)
                        mymodal.destroy();

                    ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        title: 'Save note',
                        body: Templates.render('block_notes/save_modal', opts),
                    })
                    .then(function (modal) {
                        mymodal = modal; // TODO: remove this ugly temporary solution, do it nice
                        var root = modal.getRoot();
                        root.on(ModalEvents.save, function () {
                            var notedescription = '';
                            notedescription = $('#block_notes-description').val();
                            var labelid = 0;
                            var newlabelname = $('input#block_notes-labelname').val();
                            if (data.length > 0) {
                                labelid = $('select#block_notes-label').val();
                            }
                            saveDataToServer(ctxid, blockid, imagedata, labelid, newlabelname, notedescription);
                            modal.hide();
                        });
                        modal.show();
                    });
                })
                .fail(function(data) {
                    alert('Error saving the note: ' + data.message);
                    console.log(data);
                });
        };

    return {
        initNote: function() {
        },
        cancel: function() {
            $('#note_display_over_block').hide();
            $('#make_note_button').show();
        },
        showCropTool: function() {
            // TODO: fix this dirty trick
            let dsleads = document.getElementsByClassName('ds-lead');
            if (dsleads != null)
            {
                for (var i = 0; i < dsleads.length; i++) {
                    dsleads[i].className = 'fix-ds-lead';
                }
            }

            $('#note_display_over_block').show();
            $('#make_note_button').hide();
        },
        makeScreenshot: function(crop_elem, ctxid, blockid, courseid) {
            const screenshotTarget = document.body;
            const element = document.querySelector(crop_elem);
            var rect = element.getBoundingClientRect();

            $('#note_display_over_block').hide();
            $('#note_display_wait_block').show();
            require(['block_notes/html2canvas'], function(h2c) {
                let mult = 2;
                if( /Android|webOS|iPhone|iPad|Mac|Macintosh|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
                    mult = 1;
                }
                let xx = rect.left + window.scrollX;
                let yy = rect.top + window.scrollY * mult;

                let scx = window.scrollX;
                let scy = window.scrollY;
                h2c(document.body, {
                    scale: 1,
                    x: xx,
                    y: yy,
                    scrollX: scx,
                    scrollY: scy,
                    width : rect.width,
                    height : rect.height,
                }).then(function(canvas) {
                    let base64image = canvas.toDataURL("image/png");
                    doModalDialog(ctxid, blockid, courseid, base64image);
                    $('#note_display_wait_block').hide();
                }).catch(function(error) {
                    $('#note_display_wait_block').hide();
                    alert('Unable to take a screenshot\n\n' + error.message);
                });
            });
            $('#make_note_button').show();
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