/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *
 * MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   BSS
 * @package    Bss_UrlRewriteImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    "jquery"
], function ($) {
    "use strict";
    $.widget('import.ajax', {
        _create: function () {
            $.ajax({
                url : this.options.ajaxUrl,
                type : 'post',
                dataType : 'json',
                success : function (result) {

                }
            });
        },
    });

    $('#entity').change(function () {
        if ($('#entity').val()=='bss_url_rewrite') {
            $('#basic_behavior_import_multiple_value_separator').val('|');
            $('.field-basic_behaviorfields_enclosure').css('display', 'none');
            $('.field-basic_behavior__import_field_separator').css('display', 'none');
            $('.field-basic_behavior_import_multiple_value_separator').css('display', 'none');
            $('.field-import_images_file_dir').css('display', 'none');
        } else {
            $('#basic_behavior_import_multiple_value_separator').val(',');
        }
    });

    $('#bss-version').appendTo('.field-entity .admin__field-control.control .admin__field');

    return $.import.ajax;
});
