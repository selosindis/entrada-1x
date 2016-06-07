/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Advanced search jQuery extension
 *
 * @author Organisation: Queen's University
 * @author Unit: School of Medicine
 * @author Developer: Josh Dillon <jdillon@queensu.ca>
 * @copyright Copyright 2014 Queen's University. All Rights Reserved.
 *
 */

;(function($) {
    $.fn.advancedSearch = function(options) {

        /*
         *
         *  Advanced search settings
         *
         */

        var self = this;
        var interval;
        var settings = $.extend({
            api_url: "?section=api-items",
            async: true,
            build_selected_filters: true,
            child_field: "parent_id",
            current_filter: "",
            current_filter_label: "",
            default_option_label: "-- Select a Filter --",
            default_apply_filter_label: "Apply Filters",
            default_clear_search_label: "Clear All",
            default_close_search_label: "Close Search",
            default_select_label : "Select",
            filters: "",
            filter_component_label: "Items",
            height: 400,
            interval: 500,
            level_selectable: true,
            list_data: {},
            load_data_function: "",
            min_search_length: 3,
            no_results_text: "No results found",
            parent_control_value: "",
            parent_id: 0,
            reload_page_flag: false,
            remove_filters_method: "remove-all-filters",
            resource_url: "",
            results_parent: "",
            save_filters_method: "set-filter-preferences",
            search_input_label: "Begin typing to search...",
            search_mode: true,
            search_target_form_action: "",
            selector_control_name: "",
            selector_mode: false,
            session_storage_key: "search_target_control_ids",
            total_filters: 0,
            value: 0,
            width: 300,
            build_form: true,
            apply_filter_function: function (e) {
                e.preventDefault();
                saveFilterPreferences();
            },
            clear_filter_function: function (e) {
                e.preventDefault();
                closeFilterInterface();
            },
            close_filter_function: function (e) {
                e.preventDefault();
                resetFilterSelect();
                closeFilterInterface();
            }
        }, options);

        $(this).data("settings", settings);

        /*
         *
         *  Advanced search event listeners
         *
         */

        self.on("click", function (e) {
            e.preventDefault();

            resetApiParams();
            toggleFilterMenu();
        });

        self.parent().on("click", ".filter-list-item", function () {
            applyFilter($(this).attr("data-source"), $(this).attr("data-label"));
        });

        $(document).mouseup(function (e) {
            if ((!$(".search-overlay").is(e.target) && $(".search-overlay").has(e.target).length === 0) && (!$(".filter-menu").is(e.target) && $(".filter-menu").has(e.target).length === 0)) {
                resetFilterSelect();
                closeFilterInterface();
            }
        });

        $(document).mouseup(function (e) {
            if ((!$(".btn-search-filter").is(e.target) && $(".btn-search-filter").has(e.target).length === 0) && (!$(".filter-menu").is(e.target) && $(".filter-menu").has(e.target).length === 0)) {
                closeFilterMenu();
            }
        });

        self.parent().on("keyup", ".search-input", function () {
            if (typeof settings.filters[settings.current_filter].api_params != "undefined") {
                settings.filters[settings.current_filter].api_params["context"] = "search";
            }

            clearInterval(interval);
            interval = window.setInterval(getFilterData, settings.interval);
        });

        self.parent().on("click", ".search-target-children-toggle", function (e) {
            var parent_id = $(this).closest("li").attr("data-id");
            settings.parent_id = parent_id;

            var context = $(this).closest("li").attr("data-context");

            if (context && typeof settings.filters[settings.current_filter].api_params != "undefined") {
                settings.filters[settings.current_filter].api_params["context"] = context;
            }

            getFilterChildren(parent_id, context);

            $(".search-input").focus();
            $(".search-input").val("");

            e.preventDefault();
        });

        self.parent().on("click", ".filter-ellipsis-container", function () {
            var parent_id = $(this).attr("data-parent");
            settings.parent_id = parent_id;

            var context = $(this).attr("data-context");

            if (context && typeof settings.filters[settings.current_filter].api_params != "undefined") {
                settings.filters[settings.current_filter].api_params["context"] = context;
            }

            getFilterChildren(parent_id, context);

            $(".search-input").focus();
            $(".search-input").val("");
        });

        self.parent().on("change", ".search-target-input-control", function () {
            var checked = $(this).is(":checked");
            var filter_type = $(this).attr("data-filter");

            if (jQuery(this).hasClass("select-all-targets-input-control")) {
                var ul = $(this).closest("ul.search-filter-list");

                $(ul).find(".search-target-input-control").each(function (key, element) {
                    $(this).attr("checked", checked ? "checked" : null);

                    var target_id = $(element).val();
                    var target_title = $(element).attr("data-label");
                    var parent_id = $(element).closest("li").attr("data-parent");

                    if (checked) {
                        var search_target_control_exists = $("." + filter_type + "_search_target_control[value=\"" + target_id + "\"]").length;

                        if (!search_target_control_exists || $(element).hasClass("select-all-targets-input-control")) {
                            $(element).closest("li").addClass("search-target-selected");

                            buildSearchTargetControl(target_id, target_title, filter_type, parent_id, "checkbox");
                        }

                        if (!jQuery.isEmptyObject(settings.list_data)) {
                            listData(target_id, parent_id, target_title, filter_type, checked);
                        }
                    } else {
                        var remove_targets = false;

                        if (typeof parent_id !== "undefined") {
                            if ($("#" + filter_type + "_" + parent_id + "_" + target_id).length) {
                                remove_targets = true;
                            } else {
                                // This target was selected in another context so keep it checked
                                $(this).attr("checked", "checked");
                            }
                        } else {
                            if ($("#" + filter_type + "_" + target_id).length) {
                                remove_targets = true;
                            }
                        }

                        if (remove_targets) {
                            $(element).closest("li").removeClass("search-target-selected");

                            removeFilterTag(target_id, filter_type, parent_id);
                            removeSearchTargetControl(target_id, filter_type, parent_id);

                            if (!jQuery.isEmptyObject(settings.list_data)) {
                                listData(target_id, parent_id, target_title, filter_type, checked);
                            }
                        }
                    }
                });

                if (settings.build_selected_filters) {
                    buildSelectedFilters();
                }
            } else {
                var target_id = $(this).val();
                var parent_id = $(this).closest("li").attr("data-parent");
                var target_title = $(this).attr("data-label");
                var mode = (settings.filters[settings.current_filter].hasOwnProperty("mode") ? settings.filters[settings.current_filter].mode : "checkbox");

                switch (mode) {
                    case "radio" :
                        if (checked) {
                            $("." + filter_type + "_search_target_control").remove();

                            if (!jQuery.isEmptyObject(settings.list_data)) {
                                $("#" + filter_type + "-list-container").remove();
                            }

                            $(".search-filter-item").removeClass("search-target-selected");
                            $(this).closest("li").addClass("search-target-selected");

                            buildSearchTargetControl(target_id, target_title, filter_type, parent_id, mode);

                            if (settings.filters[settings.current_filter].set_button_text_to_selected_option) {
                                buildSelectedOption(target_id, filter_type);
                            }

                            closeFilterInterface();
                        }

                        break;
                    case "checkbox" :
                        if (checked) {
                            $(this).closest("li").addClass("search-target-selected");

                            buildSearchTargetControl(target_id, target_title, filter_type, parent_id, mode);
                        } else {
                            $(this).closest("li").removeClass("search-target-selected");

                            removeFilterTag(target_id, filter_type, parent_id);
                            removeSearchTargetControl(target_id, filter_type, parent_id);

                            if (settings.filters[settings.current_filter].hasOwnProperty("select_all_enabled") &&
                                settings.filters[settings.current_filter].select_all_enabled
                            ) {
                                checkIfSelectAllIsTheOnlyTargetChecked(filter_type, parent_id);
                            }
                        }

                        if (settings.build_selected_filters) {
                            buildSelectedFilters();
                        }

                        break;
                }

                if (!jQuery.isEmptyObject(settings.list_data)) {
                    listData(target_id, parent_id, target_title, filter_type, checked);
                }
            }

            self.trigger("change", target_id);
        });

        self.parent().on("click", ".remove-target-toggle", function () {
            var parent_id = $(this).attr("data-parent");
            var select_all_clicked = $(this).attr("data-id") == 0;
            var filter_type = $(this).attr("data-filter");

            if (select_all_clicked) {
                var ul = $(this).closest("ul.selected-targets-list");

                $(ul).find("." + filter_type + "_target_item").each(function (key, element) {
                    var target_id = $(element).children(".remove-target-toggle").attr("data-id");

                    if (typeof parent_id !== "undefined") {
                        var current_parent_id = $(element).children(".remove-target-toggle").attr("data-parent");

                        if (parent_id == current_parent_id) {
                            if (target_id == "0") {
                                $("#" + filter_type + "_" + parent_id + "_target_0").prop("checked", false).closest("li").removeClass("search-target-selected");
                            } else {
                                $("#" + filter_type + "_target_" + target_id).prop("checked", false).closest("li").removeClass("search-target-selected");
                            }

                            removeFilterTag(target_id, filter_type, current_parent_id);
                            removeSearchTargetControl(target_id, filter_type, current_parent_id);

                            if (!jQuery.isEmptyObject(settings.list_data)) {
                                listData(target_id, parent_id, null, filter_type, false);
                            }
                        }
                    } else {
                        $("#" + filter_type + "_target_" + target_id).prop("checked", false).closest("li").removeClass("search-target-selected");

                        removeFilterTag(target_id, filter_type, current_parent_id);
                        removeSearchTargetControl(target_id, filter_type, current_parent_id);

                        if (!jQuery.isEmptyObject(settings.list_data)) {
                            listData(target_id, parent_id, null, filter_type, false);
                        }
                    }
                });
            } else {
                var target_id = $(this).attr("data-id");

                $("#" + filter_type + "_target_" + target_id).prop("checked", false).closest("li").removeClass("search-target-selected");

                removeFilterTag(target_id, filter_type, parent_id);
                removeSearchTargetControl(target_id, filter_type, parent_id);

                if (!jQuery.isEmptyObject(settings.list_data)) {
                    listData(target_id, parent_id, null, filter_type, false);
                }

                if (settings.filters[settings.current_filter].hasOwnProperty("select_all_enabled") &&
                    settings.filters[settings.current_filter].select_all_enabled
                ) {
                    checkIfSelectAllIsTheOnlyTargetChecked(filter_type, parent_id);
                }
            }
        });

        self.parent().on("click", ".search-clear-button", function (e) {
            e.preventDefault();
            removeAllFilters();
        });

        function buildFilterMenu () {
            var filter_container = $(document.createElement("div")).addClass("filter-menu");
            var filter_heading = $(document.createElement("h4")).addClass("filter-menu-heading").html("Select a filter type to begin");
            var filter_ul = $(document.createElement("ul")).addClass("filter-list");
            var filter_counter = 0;
            var filter_li;
            var modal = (settings.hasOwnProperty("modal") && settings.modal ? true : false);

            if (modal) {
                filter_container.addClass("fixed-position");
            } else {
                filter_container.addClass("absolute-position");
            }

            $.each(settings.filters, function (filter_name, filter_options) {
                filter_li = $(document.createElement("li")).html(filter_options.label).addClass("filter-list-item").attr({"data-source": filter_name, "data-label": filter_options.label});
                filter_ul.append(filter_li);
                filter_counter++;
            });

            settings.total_filters = filter_counter;

            filter_container.append(filter_heading).append(filter_ul);
            self.after(filter_container);
            filter_container.offset({top: (self.offset().top + ($(".btn-search-filter").height() + 20)), left: self.offset().left});

            if (filter_counter == 1) {
                applyFilter(filter_li.attr("data-source"), filter_li.attr("data-label"));
            }
        }

        function toggleFilterMenu () {
            if ($(".filter-menu").length > 0) {
                closeFilterMenu();
            } else {
                buildFilterMenu();
            }
        }

        function closeFilterMenu () {
            if ($(".filter-menu").length > 0) {
                $(".filter-menu").remove();

                if ($(".btn-search-filter").hasClass("active")) {
                    $(".btn-search-filter").removeClass("active");
                }
            }
        }

        function buildSearchInterface() {
            var container                   = $(document.createElement("div")).addClass("search-overlay").css({"width": settings.width + "px"});
            var input_container             = $(document.createElement("div")).addClass("input-container");
            var filter_container            = $(document.createElement("div")).addClass("filter-container");
            var search_input                = $(document.createElement("input")).attr({type: "text", placeholder: settings.search_input_label}).addClass("search-input");
            var button_container            = $(document.createElement("div")).addClass("search-buttons");
            var grippie_div                 = $(document.createElement("div")).addClass("grippie");
            var selected_targets_container  = $(document.createElement("div")).addClass("selected-targets-container");
            var filter_type_h4              = $(document.createElement("h4")).html("Filtering " + settings.filter_component_label + " by " + settings.current_filter_label).addClass("search-label");
            var mode = (settings.filters[settings.current_filter].hasOwnProperty("mode") ? settings.filters[settings.current_filter].mode : "checkbox");
            var modal = (settings.hasOwnProperty("modal") && settings.modal  ? true : false);

            if (modal) {
                container.addClass("fixed-position");
            } else {
                container.addClass("absolute-position");
            }

            if (mode == "checkbox") {
                var apply_filter_a  = $(document.createElement("a")).attr({href: "#"}).html(settings.default_apply_filter_label).addClass("search-apply-button").on("click", function(e) {
                    settings.apply_filter_function(e);
                });

                var close_search_a  = $(document.createElement("a")).attr({href: "#"}).html(settings.default_close_search_label).addClass("search-close-button").on("click", function(e) {
                    settings.close_filter_function(e);
                });

                var clear_a         = $(document.createElement("a")).attr({href: "#"}).html(settings.default_clear_search_label).addClass("search-clear-button").on("click", function(e) {
                    settings.clear_filter_function(e);
                });

                if (typeof settings.parent_form === "undefined") {
                    button_container.append(apply_filter_a).append(clear_a).append(close_search_a);
                }
            }

            input_container.append(search_input);
            container.append(input_container).append(filter_type_h4).append(selected_targets_container).append(filter_container).append(button_container);
            self.after(container);
            container.offset({top: (self.offset().top + ($(".btn-search-filter").height() + 20)), left: self.offset().left});

            if (settings.build_selected_filters) {
                buildSelectedFilters();
            }

            getFilterData();

            $(".search-input").focus();
        }

        function closeFilterInterface() {
            if ($(".search-overlay").length > 0) {
                resetParentID();
                $(".search-overlay").remove();
            }
        }

        function resetFilterSelect() {
            $(".filter-select").val("0");
        }

        function setHeight (height) {
            self.settings.height = height;
        }

        function setWidth (width) {
            self.settings.width = width;
        }

        function setFilterContainerHeight() {
            var filter_height = getFilterHeight();
            $(".filter-container").css("height", filter_height + "px");
        }

        function getFilterHeight() {
            var overlay_height = $(".search-overlay").height();
            var ui_component_height = $(".input-container").outerHeight() + $(".search-label").outerHeight() + $(".search-buttons").outerHeight() + $(".secondary-search-label").outerHeight();
            return (overlay_height - ui_component_height);
        }

        function showLoadingMessage() {
            var msg_div         = $(document.createElement("div")).addClass("search-loading-msg");
            var msg_p           = $(document.createElement("p")).html("Loading " + settings.current_filter_label + " data...");
            var spinner_img     = $(document.createElement("img")).attr({src: settings.resource_url + "/images/loading_small.gif"});

            msg_div.append(msg_p).append(spinner_img);
            $(".filter-container").append(msg_div);
        }

        function removeLoadingMessage() {
            if ($(".search-loading-msg").length > 0) {
                $(".search-loading-msg").remove();
            }
        }

        function removeSearchList() {
            if ($(".search-filter-list").length > 0) {
                $(".search-filter-list").remove();
            }
        }

        function resetFilterContainer() {
            $(".filter-container").children().remove(":not(.data-source-error)");
        }

        function getFilterData () {
            resetFilterContainer();

            var parent_id = settings.parent_id;
            var data_source = parent_id == 0 ? settings.filters[settings.current_filter].data_source : settings.filters[settings.current_filter].secondary_data_source;
            var extra_params = "";

            if (typeof settings.filters[settings.current_filter].api_params != "undefined") {
                $.each(settings.filters[settings.current_filter].api_params, function(param_name, param_value) {
                    extra_params += "&" + param_name + "=" + param_value;
                });
            }

            if (typeof data_source !== "undefined") {
                if (typeof data_source === "string") {
                    var search_value = $(".search-input").val();
                    var data = $.ajax(
                        {
                            url: settings.api_url,
                            data: "method=" + data_source + "&search_value=" + search_value + "&parent_id=" + parent_id + extra_params,
                            type: 'GET',
                            error: function () {
                                removeLoadingMessage();
                                showDataErrorMessage("An error occured while attempting to fetch the data for self filter. Please try again later.");
                            },
                            beforeSend: function () {
                                if ($(".search-input").val().length === 0) {
                                    removeDataErrorMessage();
                                    showLoadingMessage();
                                }
                            },
                            async: settings.async
                        }
                    );

                    $.when(data).done(
                        function (data) {
                            removeLoadingMessage();

                            var jsonResponse = $.parseJSON(data);

                            switch (jsonResponse.status) {
                                case "success" :
                                    removeDataErrorMessage();
                                    displayFilterData(jsonResponse.data, jsonResponse.level_selectable, jsonResponse.parent_name);

                                    if (settings.build_selected_filters) {
                                        buildSelectedFilters();
                                    }

                                    break;
                                case "error" :
                                    showDataErrorMessage(jsonResponse.data);

                                    break;
                            }
                        }
                    );
                } else if (typeof data_source === "object") {
                    displayFilterData(data_source);
                }
            } else {
                showDataErrorMessage("No data source supplied.");
            }

            clearInterval(interval);
        }

        function getFilterChildren (parent_id, context) {
            resetFilterContainer();
            var data_source = settings.filters[settings.current_filter].secondary_data_source;
            var extra_params = "";

            if (typeof settings.filters[settings.current_filter].api_params !== "undefined") {
                $.each(settings.filters[settings.current_filter].api_params, function(param_name, param_value) {
                    extra_params += "&" + param_name + "=" + param_value;
                });
            }

            if (typeof data_source !== "undefined") {
                if (typeof data_source === "string") {
                    var data = $.ajax(
                        {
                            url: settings.api_url,
                            data: "method=" + data_source + "&parent_id=" + parent_id + extra_params + (typeof context != "undefined" ? "&context=" + context : ""),
                            type: "GET",
                            error: function () {
                                showDataErrorMessage("An error occured while attempting to fetch the data for self filter. Please try again later.");
                            },
                            beforeSend: function () {
                            },
                            async: settings.async
                        }
                    );

                    $.when(data).done(
                        function (data) {
                            var jsonResponse = JSON.parse(data);

                            removeDataErrorMessage();

                            if (jsonResponse.parent_name !== "0") {
                                buildSecondarySearchLabel(jsonResponse.parent_id, jsonResponse.parent_name);
                            } else {
                                removeSecondaryLabel();
                            }

                            if (typeof settings.filters[settings.current_filter].api_params != "undefined") {
                                $.each(settings.filters[settings.current_filter].api_params, function (param_name) {
                                    if (jsonResponse.hasOwnProperty(param_name)) {
                                        settings.filters[settings.current_filter].api_params[param_name] = jsonResponse[param_name];
                                    }
                                });
                            }

                            switch (jsonResponse.status) {
                                case "success" :
                                    displayFilterData(jsonResponse.data, jsonResponse.level_selectable, jsonResponse.parent_name);

                                    break;
                                case "error" :
                                    showDataErrorMessage(jsonResponse.data);

                                    break;
                            }
                        }
                    );
                } else if (typeof data_source === "object") {
                    displayFilterData(data_source);
                }
            } else {
                showDataErrorMessage("No data source supplied.");
            }
        }

        function displayFilterData (data, level_selectable, parent_name) {
            var targets_ul = $(document.createElement("ul")).addClass("search-filter-list");
            var mode = (settings.filters[settings.current_filter].hasOwnProperty("mode") ? settings.filters[settings.current_filter].mode : "checkbox");
            var is_selectable = (typeof settings.level_selectable == "boolean" ? settings.level_selectable : true);
            var context;

            if (typeof level_selectable == "boolean") {
                is_selectable = level_selectable;
            } else if (data.hasOwnProperty("level_selectable")) {
                is_selectable = data.level_selectable;
            }

            if (mode == "checkbox" && is_selectable && settings.filters[settings.current_filter].hasOwnProperty("select_all_enabled") && settings.filters[settings.current_filter].select_all_enabled && !$(".search-input").val()) {
                var target_li               = $(document.createElement("li")).addClass("search-filter-item").attr({"data-id": 0});
                var target_li_div           = $(document.createElement("div")).addClass("search-target-controls");
                var target_label_span       = $(document.createElement("span")).addClass("search-target-label");
                var target_label            = $(document.createElement("label")).html("Select All").addClass("search-target-label-text").attr({"for": settings.current_filter + (typeof data[0].target_parent != "undefined" ? "_" + data[0].target_parent : "") + "_target_0"});
                var target_input_span       = $(document.createElement("span")).addClass("search-target-input");
                var target_input;
                var target_input_label = "Select All";

                if (typeof data[0].target_parent != "undefined") {
                    target_li.attr({"data-parent": data[0].target_parent});
                }

                if (typeof parent_name != "undefined") {
                    target_input_label += " From " + parent_name;
                } else if (data.hasOwnProperty("parent_name")) {
                    target_input_label += " From " + data.parent_name;
                }

                target_input = $(document.createElement("input")).attr({
                    type: "checkbox",
                    id: settings.current_filter + (typeof data[0].target_parent != "undefined" ? "_" + data[0].target_parent : "") + "_target_0",
                    "data-label": target_input_label,
                    "data-filter": settings.current_filter
                }).val(0).addClass("search-target-input-control").addClass("select-all-targets-input-control");

                target_label_span.append(target_label);

                if (typeof target_input !== "undefined") {
                    target_input_span.append(target_input);
                }

                target_li_div.append(target_label_span).append(target_input_span);
                target_li.append(target_li_div);
                targets_ul.append(target_li);

                if ($("#" + settings.current_filter + (typeof data[0].target_parent != "undefined" ? "_" + data[0].target_parent : "") + "_0").length > 0) {
                    target_input.attr("checked", "checked");
                    target_li.addClass("search-target-selected");
                }
            }

            if (settings.filters[settings.current_filter].hasOwnProperty("api_params") && settings.filters[settings.current_filter].api_params.hasOwnProperty("next_context")) {
                context = "next";
            }

            $.each(data, function (key, filter_target) {
                var hilited_search_value    = hiliteSearchValue(filter_target.target_label, $(".search-input").val());
                var target_li               = $(document.createElement("li")).addClass("search-filter-item").attr({"data-id": filter_target.target_id, "data-parent" : filter_target.target_parent});
                var target_li_div           = $(document.createElement("div")).addClass("search-target-controls");
                var target_label_span       = $(document.createElement("span")).addClass("search-target-label");
                var target_label            = $(document.createElement("label")).html(hilited_search_value).addClass("search-target-label-text").attr({"for": settings.current_filter +"_target_" + filter_target.target_id});
                var target_input_span       = $(document.createElement("span")).addClass("search-target-input");
                var target_input;

                if (typeof context != "undefined") {
                    target_li.attr({"data-context": context});
                }

                if (is_selectable) {
                    switch (mode) {
                        case "radio" :
                            target_input = $(document.createElement("input")).attr({
                                type: "radio",
                                id: settings.current_filter +"_target_" + filter_target.target_id,
                                name: "selected-target",
                                "data-label": filter_target.target_label,
                                "data-filter": settings.current_filter
                            }).val(filter_target.target_id).addClass("search-target-input-control");

                            break;
                        case "checkbox" :
                            target_input = $(document.createElement("input")).attr({
                                type: "checkbox",
                                id: settings.current_filter +"_target_" + filter_target.target_id,
                                "data-label": filter_target.target_label,
                                "data-filter": settings.current_filter
                            }).val(filter_target.target_id).addClass("search-target-input-control");

                            break;
                    }
                }

                if (filter_target.hasOwnProperty("target_children")) {
                    if (filter_target.target_children > 0) {
                        var target_children_span = $(document.createElement("a")).attr({href: "#"}).html("+").addClass("search-target-children-toggle").attr({"data-label": filter_target.target_label});
                        target_li_div.append(target_children_span);
                    }
                }

                target_label_span.append(target_label);

                if (typeof target_input !== "undefined") {
                    target_input_span.append(target_input);
                }

                target_li_div.append(target_label_span).append(target_input_span);
                target_li.append(target_li_div);
                targets_ul.append(target_li);

                if ($("." + settings.current_filter + "_search_target_control[value=\"" + filter_target.target_id + "\"]").length && typeof target_input !== "undefined") {
                    target_input.attr("checked", "checked");
                    target_li.addClass("search-target-selected");
                }
            });

            $(".filter-container").append(targets_ul);
        }

        function buildSecondarySearchLabel (parent_id, parent_name) {
            removeSecondaryLabel();
            var heading_label                   = parent_name;
            var filter_ellipsis_span            = $(document.createElement("a")).addClass("filter-ellipsis-container").attr({"data-parent": parent_id});
            var secondary_heading_container     = $(document.createElement("div")).addClass("secondary-search-container");
            var secondary_heading_h4_container  = $(document.createElement("div")).addClass("secondary-search-label-container");
            var secondary_nav_container         = $(document.createElement("div")).addClass("secondary-search-nav-container");
            var secondary_heading               = $(document.createElement("h4")).html(heading_label).addClass("secondary-search-label");

            if (settings.filters[settings.current_filter].hasOwnProperty("api_params") && settings.filters[settings.current_filter].api_params.hasOwnProperty("previous_context")) {
                filter_ellipsis_span.attr({"data-context": "previous"});
            }

            secondary_heading_h4_container.append(secondary_heading);
            secondary_nav_container.append(filter_ellipsis_span);
            secondary_heading_container.append(secondary_nav_container).append(secondary_heading_h4_container);
            $(".search-label").after(secondary_heading_container);
        }

        function showDataErrorMessage (msg) {
            if ($(".data-source-error").length === 0) {
                var error_p = $(document.createElement("p")).html(msg).addClass("data-source-error");
                $(".filter-container").append(error_p);
            }
        }

        function removeDataErrorMessage () {
            if ($(".data-source-error").length > 0) {
                $(".data-source-error").remove();
            }
        }

        function setCurrentFilter (current_filter, current_filter_label) {
            settings.current_filter = current_filter;
            settings.current_filter_label = current_filter_label;
        }

        function hiliteSearchValue (string, needle) {
            var search_input = $(".search-input").val();

            if (search_input && search_input.length > 0) {
                return string.replace(new RegExp('(^|)(' + $.trim(needle) + ')(|$)','ig'), '$1<span class=\"search-hilite\">$2</span>$3');
            }

            return string;
        }

        function resetParentID () {
            settings.parent_id = 0;
        }

        function removeSecondaryLabel () {
            if ($(".secondary-search-container").length > 0) {
                $(".secondary-search-container").remove();
            }
        }

        function removeFilterTag(target_id, filter_type, parent_id) {
            if (typeof parent_id === "undefined") {
                if ($("." + filter_type + "_" + target_id).length > 0) {
                    $("." + filter_type + "_" + target_id).remove();
                }
            } else {
                if ($("." + filter_type + "_" + parent_id + "_" + target_id).length > 0) {
                    $("." + filter_type + "_" + parent_id + "_" + target_id).remove();
                }
            }

            if ($("." + filter_type + "_target_item").length === 0) {
                if ($("#" + filter_type + "_targets_container").length > 0) {
                    $("#" + filter_type + "_targets_container").remove();
                }
            }
        }

        function removeSelectedTargetsList() {
            if ($(".selected-targets-list").length > 0) {
                $(".selected-targets-list").remove();
                $(".selected-targets-heading").remove();
            }
        }

        function clearAllFilters() {
            if ($(".selected-targets-container").length > 0) {
                $(".selected-targets-container").remove();
            }

            if ($(".search-filter-item").hasClass("search-target-selected")) {
                $(".search-filter-item").removeClass("search-target-selected");
            }

            if ($(".search-target-input-control").is(":checked")) {
                $(".search-target-input-control").prop("checked", false);
            }

            if ($("#search-targets-form").length > 0) {
                $("#search-targets-form").remove();
            }

            closeFilterInterface();
        }

        function buildSelectedFilters() {
            if ($(".selected-targets-container").length > 0) {
                $(".selected-targets-container").remove();
            }

            var selected_targets_container = $(document.createElement("div")).addClass("selected-targets-container");

            $.each(settings.filters, function (filter, options) {
                if ($("." + filter + "_search_target_control").length > 0) {
                    var filter_container            = $(document.createElement("div")).attr({id: filter + "_targets_container"}).addClass("targets-container");
                    var selected_targets_heading    = $(document.createElement("h4")).addClass("selected-targets-heading").html("Selected " + options.label);
                    var selected_targets_ul         = $(document.createElement("ul")).addClass(filter +"_selected_targets_list").addClass("selected-targets-list");
                    var mode = (settings.filters[settings.current_filter].hasOwnProperty("mode") ? settings.filters[settings.current_filter].mode : "checkbox");

                    if (mode == "checkbox") {
                        $.each($("." + filter + "_search_target_control"), function (key, input) {
                            var parent_id = $(this).attr("data-parent");

                            if (typeof parent_id != "undefined") {
                                var target_id = $(this).attr("data-id");
                                var target_label = $(this).attr("data-label");
                                var target_item = $(document.createElement("li")).addClass("selected-target-item").addClass(filter + "_" + parent_id + "_" + target_id).addClass(filter + "_target_item").html("<span class=\"target-label\">" + target_label + "</span>");
                                var target_item_span = $(document.createElement("span")).addClass("remove-target-toggle").attr({
                                    "data-id": target_id,
                                    "data-parent": parent_id,
                                    "data-filter": filter
                                }).html("&times;");

                                target_item.append(target_item_span);
                                selected_targets_ul.append(target_item);

                                filter_container.append(selected_targets_heading).append(selected_targets_ul);
                                selected_targets_container.append(filter_container);
                                $(".input-container").after(selected_targets_container);
                            } else {
                                var target_id = $(this).attr("data-id");
                                var target_label = $(this).attr("data-label");
                                var target_item = $(document.createElement("li")).addClass("selected-target-item").addClass(filter + "_" + target_id).addClass(filter + "_target_item").html("<span class=\"target-label\">" + target_label + "</span>");
                                var target_item_span = $(document.createElement("span")).addClass("remove-target-toggle").attr({
                                    "data-id": target_id,
                                    "data-filter": filter
                                }).html("&times;");

                                target_item.append(target_item_span);
                                selected_targets_ul.append(target_item);

                                filter_container.append(selected_targets_heading).append(selected_targets_ul);
                                selected_targets_container.append(filter_container);
                                $(".input-container").after(selected_targets_container);
                            }
                        });
                    }
                }
            });
        }

        function buildSearchTargetsForm() {
            var form = $(document.createElement("form")).attr({id: "search-targets-form", method: "post", action: settings.search_target_form_action});
            settings.results_parent.after(form);
        }

        function buildSearchTargetControl(target_id, target_title, filter_type, parent_id, mode) {
            switch (mode) {
                case "radio" :
                    var search_target_control = $(document.createElement("input")).attr({
                        type: "hidden",
                        name: settings.filters[filter_type].selector_control_name,
                        value: target_id,
                        id: filter_type + "_" + target_id,
                        "data-id": target_id,
                        "data-label": target_title,
                        "data-filter": filter_type
                    }).addClass("search-target-control").addClass(filter_type + "_search_target_control");
                    settings.selected_list_container.append(search_target_control);
                    break;
                case "checkbox" :
                    if (typeof settings.selected_list_container === "undefined") {
                        if ($("#search-targets-form").length === 0) {
                            buildSearchTargetsForm();
                        }

                        if ($("#search-targets-form").length > 0 && $("#" + filter_type + "_" + target_id).length === 0) {
                            createSearchTargetControlForCheckboxes(target_id, target_title, filter_type, parent_id, $("#search-targets-form"));
                        }
                    } else {
                        createSearchTargetControlForCheckboxes(target_id, target_title, filter_type, parent_id, settings.selected_list_container);
                    }
                    break;
            }
        }

        function checkIfSelectAllIsTheOnlyTargetChecked(filter_type, parent_id) {
            // If there is only one target selected in a context and it is the select all, deselect it.
            if ($("." + filter_type + "_search_target_control[data-parent=" + parent_id + "]").length == 1 &&
                $("." + filter_type + "_search_target_control[data-parent=" + parent_id + "]").val() == 0
            ) {
                // If a target from the current context was removed, remove the styles from the select all
                if (parent_id == settings.parent_id) {
                    var select_all = $(".select-all-targets-input-control:checked");

                    select_all.closest("li").removeClass("search-target-selected");
                    select_all.prop("checked", false);
                }

                removeFilterTag(0, filter_type, parent_id);
                removeSearchTargetControl(0, filter_type, parent_id);

                if (!jQuery.isEmptyObject(settings.list_data)) {
                    listData(0, parent_id, null, filter_type, false);
                }
            }
        }

        function createSearchTargetControlForCheckboxes(target_id, target_title, filter_type, parent_id, container) {
            var search_target_control = $(document.createElement("input")).attr({
                type: "hidden",
                name: filter_type + (typeof parent_id != "undefined" ? "_" + parent_id : "") + "[]",
                value: target_id,
                id: filter_type + (typeof parent_id != "undefined" ? "_" + parent_id : "") + "_" + target_id,
                "data-id": target_id,
                "data-label": target_title,
                "data-filter": filter_type
            }).addClass("search-target-control").addClass(filter_type + "_search_target_control");

            if (typeof parent_id != "undefined") {
                search_target_control.attr("data-parent", parent_id);
            }

            container.append(search_target_control);
        }

        function removeSearchTargetControl(target_id, filter_type, parent_id) {
            if (target_id == 0) {
                if (typeof parent_id === "undefined") {
                    if ($("#" + filter_type + "_" + target_id).length > 0) {
                        $("#" + filter_type + "_" + target_id).remove();
                    }
                } else {
                    if ($("#" + filter_type + "_" + parent_id + "_" + target_id).length > 0) {
                        $("#" + filter_type + "_" + parent_id + "_" + target_id).remove();
                    }
                }
            } else {
                $("input[value=" + target_id + "]." + filter_type + "_search_target_control").remove();
            }
        }

        function loadExternalData() {
            var fn = window[settings.load_data_function];
            if(typeof fn === 'function') {
                fn(false);
            }
        }

        function saveFilterPreferences() {
            if (settings.reload_page_flag) {
                var filters = jQuery("#search-targets-form").serialize();
                var preference_data = $.ajax({
                    url: settings.api_url,
                    data: "method="+ settings.save_filters_method + "&" + filters,
                    type: "POST"
                });

                $.when(preference_data).done(function (data) {
                    var jsonResponse = JSON.parse(data);
                    if (jsonResponse.status === "success") {
                        window.location.reload();
                    }
                });
            } else {
                jQuery("#search-targets-form").submit();
            }
        }

        function removeAllFilters() {
            var remove_filters_request = $.ajax({
                url: settings.api_url,
                data: "method=" + settings.remove_filters_method,
                type: "POST"
            });

            $.when(remove_filters_request).done(function (data) {
                var jsonResponse = JSON.parse(data);
                if (jsonResponse.status === "success") {
                    window.location.reload();
                }
            });
        }

        function applyFilter(data_source, data_label) {
            closeFilterMenu();
            setCurrentFilter(data_source, data_label);
            buildSearchInterface();
        }

        function buildSelectedOption (target_id, filter_type) {
            var label  = $("#" + filter_type + "_" + target_id).attr("data-label");
            var icon   = $(document.createElement("i")).addClass("icon-chevron-down pull-right btn-icon");

            $(".target-selected").remove();
            self.html(label).append("&nbsp;").append(icon);

            if (settings.total_filters > 1) {
                var filter = settings.filters[filter_type].label;
                var selected_filter_label = $(document.createElement("span")).addClass("selected-filter-label").html(filter);
                self.prepend(selected_filter_label);
            }
        }

        function saveInitialApiParams(filter, api_params) {
            filter.initial_api_params = {};

            for (var param in api_params) {
                filter.initial_api_params[param] = api_params[param];
            }
        }

        function resetApiParams() {
            $.each(settings.filters, function (filter_name, filter_options) {
                if (typeof filter_options.api_params !== "undefined") {
                    for (var param in filter_options.api_params) {
                        filter_options.api_params[param] = filter_options.initial_api_params[param];
                    }
                }
            });
        }

        function listData(target_id, parent_id, label, filter_type, checked) {
            var list_container = $(settings.list_data.selector);
            var filter_list = $("#" + filter_type + "-list");
            var li_id = filter_type + "-list-" + (parent_id ? parent_id + "-" : "") + target_id;

            if (checked) {
                var ul;
                var li_id_exists;

                if (filter_list.length) {
                    ul = $("#" + filter_type + "-list");
                } else {
                    var div = $(document.createElement("div")).attr({id: filter_type + "-list-container"});
                    ul = $(document.createElement("ul")).attr({id: filter_type + "-list"});

                    div.append(ul);
                    list_container.append(div);
                }

                if (parent_id && target_id == 0) {
                    li_id_exists = $("#" + filter_type + "-list").find(".selected-list-item[data-id=0][data-parent=" + parent_id + "]").length;
                } else {
                    li_id_exists = $("#" + filter_type + "-list").find(".selected-list-item[data-id=" + target_id + "]").length;
                }

                if (!li_id_exists) {
                    var li = $(document.createElement("li")).attr({"id": li_id, "class": "selected-list-item", "data-id": target_id, "data-parent": (parent_id ? parent_id : "") , "data-filter": filter_type}).html(label).css("list-style-type", "none");
                    var span_container = $(document.createElement("span")).addClass("pull-right selected-item-container");
                    var span_label = $(document.createElement("span")).addClass("selected-item-label").html(settings.filters[filter_type].label);
                    var span_remove = $(document.createElement("span")).addClass("remove-list-item").html("&times;");

                    if (typeof settings.filters[filter_type].badge_background_color !== "undefined") {
                        span_label.css("background-color", settings.filters[filter_type].badge_background_color);
                    }

                    if (typeof settings.filters[filter_type].badge_text_color !== "undefined") {
                        span_label.css("color", settings.filters[filter_type].badge_text_color);
                    }

                    if (typeof settings.list_data.background_value !== "undefined") {
                        li.css("background", settings.list_data.background_value);
                    }

                    span_container.append(span_label).append(span_remove);
                    li.append(span_container);
                    ul.append(li);
                }
            } else {
                if (parent_id && target_id == 0) {
                    filter_list.find(".selected-list-item[data-id=" + target_id + "][data-parent=" + parent_id + "]").remove();
                } else {
                    filter_list.find(".selected-list-item[data-id=" + target_id + "]").remove();
                }

                if (filter_list.is(":empty")) {
                    $("#" + filter_type + "-list-container").remove();
                }
            }
        }

        $(settings.list_data.selector).on("click", ".remove-list-item", function (e) {
            e.preventDefault();

            var target_id = $(this).closest("li").attr("data-id");
            var parent_id = $(this).closest("li").attr("data-parent");
            var filter_type = $(this).closest("li").attr("data-filter");
            var select_all_clicked = target_id == 0;

            if (parent_id) {
                if (select_all_clicked) {
                    $("input[name=\"" + filter_type + "_" + parent_id + "[]\"]").each(function (key, element) {
                        target_id = $(this).attr("data-id");

                        $(this).remove();

                        listData(target_id, parent_id, null, filter_type, false);
                    });
                } else {
                    $("#" + filter_type + "_" + parent_id + "_" + target_id).remove();

                    listData(target_id, parent_id, null, filter_type, false);

                    if ($("input[name=\"" + filter_type + "_" + parent_id + "[]\"]").length == 1 && $("input[name=\"" + filter_type + "_" + parent_id + "[]\"]").val() == 0) {
                        $("input[name=\"" + filter_type + "_" + parent_id + "[]\"]").remove();

                        listData(0, parent_id, null, filter_type, false);
                    }
                }
            } else {
                if (select_all_clicked) {
                    $("input[name=\"" + filter_type + "[]\"]").each(function (key, element) {
                        target_id = $(this).attr("data-id");

                        $(this).remove();

                        listData(target_id, null, null, filter_type, false);
                    });
                } else {
                    $("#" + filter_type + "_" + target_id).remove();

                    listData(target_id, null, null, filter_type, false);

                    if ($("input[name=\"" + filter_type + "[]\"]").length == 1 && $("input[name=\"" + filter_type + "[]\"]").val() == 0) {
                        $("input[name=\"" + filter_type + "[]\"]").remove();

                        listData(0, null, null, filter_type, false);
                    }
                }
            }
        });

        function storeEachSearchTargetControl() {
            sessionStorage.removeItem(settings.session_storage_key);

            var search_target_control_ids = settings.selected_list_container.html();

            sessionStorage.setItem(settings.session_storage_key, search_target_control_ids);
        }

        function createHiddenInputOfSearchTargetControls() {
            var input_search_target_controls = $(document.createElement("input")).attr({
                type: "hidden",
                name: settings.session_storage_key
            });

            $(".search-target-control").each(function (key, element) {
                if (element.value != 0) {
                    input_search_target_controls.val(input_search_target_controls.val() + element.value + ",");
                }
            });

            var current_value = input_search_target_controls.val();
            var new_value = current_value.substr(0, current_value.length - 1);

            input_search_target_controls.val(new_value);

            settings.parent_form.append(input_search_target_controls);
        }

        if (typeof settings.parent_form !== "undefined") {
            settings.parent_form.submit(function (e) {
                if ($(".search-target-control").length) {
                    createHiddenInputOfSearchTargetControls();

                    storeEachSearchTargetControl();
                }
            });
        }

        if (sessionStorage.getItem(settings.session_storage_key)) {
            settings.selected_list_container.html(sessionStorage.getItem(settings.session_storage_key));

            sessionStorage.removeItem(settings.session_storage_key);

            $(".search-target-control").each(function (key, element) {
                var parent_id = null;
                var filter_type = $(element).attr("data-filter");

                if ($(element).attr("data-parent")) {
                    parent_id = $(element).attr("data-parent");
                }

                if (!jQuery.isEmptyObject(settings.list_data)) {
                    listData(element.value, parent_id, $(element).attr("data-label"), filter_type, true);
                }

                if (settings.filters[filter_type].mode == "radio" && settings.filters[filter_type].set_button_text_to_selected_option) {
                    var filter_counter = 0;

                    $.each(settings.filters, function (filter_name, filter_options) {
                        filter_counter++;
                    });

                    settings.total_filters = filter_counter;

                    buildSelectedOption(element.value, filter_type);
                }
            });
        }

        self.parent().addClass("entrada-search-widget");
        self.addClass("btn-search-filter");
        if (!jQuery.isEmptyObject(settings.list_data) && settings.list_data.hasOwnProperty("selector")) {
            $(settings.list_data.selector).addClass("entrada-search-list");
        }

        if (typeof settings.filters == "object") {
            for (var filter in settings.filters) {
                if (typeof settings.filters[filter].api_params !== "undefined" && typeof settings.filters[filter].initial_api_params === "undefined") {
                    saveInitialApiParams(settings.filters[filter], settings.filters[filter].api_params);
                }
            }
        }
    };
}(jQuery));
