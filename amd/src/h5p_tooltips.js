import jQuery from 'jquery';

export const init = () => {
    jQuery(window).on('load', function () {
        jQuery(window.frames).each(function (index) {
            jQuery(this).ready(function () {
                let iframe = jQuery(this).find('iframe.h5p-iframe.h5p-initialized');
                if (iframe !== undefined) {
                    jQuery(iframe).ready(function () {
                        let buttons = jQuery(iframe).contents().find('button.toolbar-button');
                        buttons.each(function () {
                            let button = jQuery(this);
                            let tooltip = button.attr('aria-label');

                            button.attr('data-toggle', 'tooltip');
                            button.attr('data-placement', 'top');
                            button.attr('title', tooltip);
                        });
                    });
                }
            });
        });
    });
};
