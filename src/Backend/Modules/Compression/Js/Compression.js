/**
 * Interaction for the compression module
 *
 * @author Jesse Dobbelaere <jesse@dobbelaere-ae.be>
 */

var settings;
jsBackend.compression =
{
    settings:
    {
        consoleFinished: false,
        tree: '.js-tree-folders',
        dummyFolders: 'input#dummyFolders',
        consoleBtn: '.js-btn-console',
        consoleWindow: '.js-console',
        logFilePath: '/src/Backend/Cache/Compression/output.log'
    },

    // init, something like a constructor
    init: function ()
    {
        settings = jsBackend.compression.settings;
        jsBackend.compression.jsTree();
        jsBackend.compression.console();
    },

    /**
     * Build the JsTree directory structure
     */
    jsTree: function ()
    {
        // Init jstree
        $(settings.tree).jstree({
            "checkbox" : {
                "tie_selection" : false
            },
            "plugins": ["checkbox"]
        });

        // Check the checkboxes that have class "checked" on page load
        $.each($(settings.tree).jstree(true)._model.data, function(i, value)
        {
            $this = $(this);

            if ($this.prop("li_attr")) {
                var $listAttribute = $this[0].li_attr;

                if ($listAttribute.class) {
                    if($listAttribute.class == "checked") {
                        $(settings.tree).jstree(true).check_node(i);
                    }
                }
            }
        });

        function addAllFolders(data) {
            var $selectedNodes = $(settings.tree).jstree(true).get_checked(true);

            var $allFolders = [];
            for (var i=0; i < $selectedNodes.length; i++) {
                var $path = $(settings.tree).jstree(true).get_node($selectedNodes[i]).data.path;
                $allFolders.push($path);

                if (typeof data.node  !== "undefined") {
                    var $parents = data.node.parents;

                    for (var j=0; j < $parents.length-1; j++) {
                        var $parent = $(settings.tree).jstree(true).get_node($parents[j]).data.path;
                        $allFolders.push($parent);
                    }
                }

            }

            // Save the folders to html
            jsBackend.compression.save($allFolders);
        }

        // Listen for events
        $(settings.tree).on('check_node.jstree', function (e, data) {
            addAllFolders(data);
        });

        // Listen for events
        $(settings.tree).on('uncheck_node.jstree', function (e, data) {
            addAllFolders(data);
        });
    },

    /**
     * Save all folders to a hidden field so we can process it in PHP.
     */
    save: function($folders)
    {
        // remove all folders before reading them
        $('input.folders').remove();

        // Add hidden folders to parse in PHP
        $.each($folders, function(index, folder)
        {
            // create a element based on this hidden field
            var $folderInput = $(settings.dummyFolders).clone();
            $folderInput.attr('name', 'folders[]');
            $folderInput.attr('id', '');
            $folderInput.addClass('folders');
            $folderInput.val(folder);

            $('.jstree-wrapper').append($folderInput);
        });

    },

    /**
     * Execute the compression script with output in a console window
     */
    console: function() {

        $(settings.consoleBtn).on('click', function(e) {
            e.preventDefault();
            settings.consoleFinished = false;

            // Animate the button
            $(settings.consoleBtn).find('span').addClass('btn-processing');

            // Erase logfile and console first
            $.ajax({
                data: {
                    fork: {
                      module: 'Compression',
                      action: 'ConsoleErase'
                    }
                },
                success: function (result) {
                    if (result.code === 200) {
                        setTimeout(jsBackend.compression.consoleCompressImages(), 50);

                        setTimeout(function() {
                            setTimeout(jsBackend.compression.consolePeriodicRead(), 50);
                        }, 1500);
                    }
                }
            });

        });
    },

    /**
     * Periodically read the file to the console window
     */
    consolePeriodicRead: function() {
        (function periodicOutput() {
            $.ajax({
                url: settings.logFilePath,
                type: 'GET',
                dataType: 'text',
                success: function (data) {
                    $(settings.consoleWindow).html(data);
                    console.log("File read");

                    if(!settings.consoleFinished) {
                        setTimeout(periodicOutput, 500);
                    } else {
                        // Stop animating the button
                        $(settings.consoleBtn).find('span').removeClass('btn-processing');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log("Can't read file");
                    if(!settings.consoleFinished) {
                        setTimeout(periodicOutput, 500);
                    } else {
                        // Stop animating the button
                        $(settings.consoleBtn).find('span').removeClass('btn-processing');
                    }
                }
            });
        })();
    },

    /**
     * Start the compression of the images
     */
    consoleCompressImages: function() {
        $.ajax({
            data: {
                fork: { module: 'Compression', action: 'StartCompression' }
            },
            success: function (result) {
                if (result.code === 200) {
                    console.log("Finished image compression");
                    settings.consoleFinished = true;
                }
            },
            error: function (request, status, error) {
                console.log('error');
                console.log(error);
            },
            timeout: 120000 // 2minutes
        });
    }

};

$(jsBackend.compression.init);
