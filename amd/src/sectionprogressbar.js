import jQuery from 'jquery';
import {call as fetchMany} from 'core/ajax';

export const get_progress = (section) => fetchMany([{
    methodname: 'theme_sdg_boost_union_section_completion',
    args: {
        section
    }
}])[0];

export const get_config = (configname) => fetchMany([{
    methodname: 'theme_sdg_boost_union_config',
    args: {
        configname
    }
}])[0];

export const init = async (section) => {
    let color = await get_config('progressbarcolorsetting');
    let defaultColor = await get_config('defaultprogressbarcolorsetting');
    let borderColor = '#27B049';
    color = color ? "background-color: " + color.toString() + " !important;" : '';
    defaultColor = defaultColor ? "background-color: " + defaultColor.toString() + " !important;" : '';


    let percentage = await get_progress(section);

    if (percentage >= 0) {
        let html = "<div class='row mb-2'><div class='col'>" +
            "<div class='progress float-right' style='width:33%; height: 1.2rem; " + defaultColor + "; " +
            "border-radius: 0.75rem; border: 1px solid " + borderColor + ";' " +
            "data-toggle='tooltip' data-placement='bottom' title='" + percentage + " %'>" +
            "<div class='progress-bar bg-success' role='progressbar' style='width: " + percentage + "%;" +
            color + "' aria-valuenow='" + percentage + "'  aria-valuemin='0' aria-valuemax='100'>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</div>";
        jQuery('.course-section-header').last().after(html);
    }
};
