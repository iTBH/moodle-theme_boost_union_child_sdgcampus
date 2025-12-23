import jQuery from 'jquery';
import {call as fetchMany} from 'core/ajax';
import Templates from 'core/templates';

export const get_config = (configname) => fetchMany([{
    methodname: 'theme_sdg_boost_union_config',
    args: {
        configname
    }
}])[0];

export const init = async() => {

    let color = await get_config('progressbarcolorsetting');
    let defaultColor = await get_config('defaultprogressbarcolorsetting');
    let borderColor = '#27B049';
    color = color ? "background-color: " + color.toString() + " !important;" : '';
    defaultColor = defaultColor ? "background-color: " + defaultColor.toString() + " !important;" : '';

    jQuery(window).ready(function () {
        jQuery('.section.course-section').each(function (index) {

            let activitycount = jQuery(this).find('.d-flex.ms-auto.align-items-center').last();

            if (activitycount.length === 1) {
                let info = activitycount.text().trim();
                info = info.split(':')[1];
                info = info.split('/');

                let current = parseInt(info[0]);
                let max = parseInt(info[1]);
                let percentage = (current / max) * 100;

                if (!isNaN(percentage)) {
                    percentage = Math.round(percentage);
                    let html = "<div class='progress ms-auto' style='width:33%; height: 1.2rem; " + defaultColor + "; " +
                        "border-radius: 0.75rem; border: 1px solid " + borderColor + ";' " +
                        "data-toggle='tooltip' data-placement='bottom' title='" + percentage + " %'>" +
                        "<div class='progress-bar bg-success' role='progressbar' style='width: " + percentage + "%;" +
                        color + "' aria-valuenow='" + percentage + "' aria-valuemin='0' aria-valuemax='100'>" +
                        "</div>" +
                        "</div>";
                    activitycount.parent().html(html);
                }
            }
        });
    });
};
