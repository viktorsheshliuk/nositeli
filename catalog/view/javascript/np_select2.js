/*
 * Nova Poshta Select2 integration for Simplecheckout.
 * Поля city / address_1 должны быть типа "Text" в настройках Simple.
 * Пользователь видит только Select2-список; значение пишется в скрытый input,
 * поэтому ввести произвольный текст невозможно.
 */
(function ($) {
    'use strict';

    var container = '#simplecheckout_shipping_address';

    function ShippingData() {
        var self = this;

        self.methods_city = ['novaposhta.department', 'novaposhta.doors', 'novaposhta.parcelbox'];
        self.methods_address = ['novaposhta.department', 'novaposhta.parcelbox'];

        self.setProp = function () {
            self.method = $('input[name="shipping_method"]:checked').val() || $('select[name="shipping_method"]').val();
        };

        self.isActive = function () {
            return $.inArray(self.method, self.methods_city.concat(self.methods_address)) != -1;
        };
    }

    var shippingData = new ShippingData();

    function cityInputs() {
        var $inputs = $(container + ' input[name*="city"]:not([type="hidden"])');
        return $inputs.length ? $inputs : $('input[name*="city"]:not([type="hidden"])');
    }

    function addrInputs() {
        var $inputs = $(container + ' input[name*="address_1"]:not([type="hidden"])');
        return $inputs.length ? $inputs : $('input[name*="address_1"]:not([type="hidden"])');
    }

    function cityFilter() {
        return $('[name*="zone"]:visible').val() || '';
    }

    function addrAction() {
        return shippingData.method == 'novaposhta.parcelbox' ? 'getPoshtomats' : 'getDepartments';
    }

    function addrFilter() {
        // if (shippingData.method == 'novaposhta.parcelbox') {
        //     return cityInputs().first().val() || '';
        // }
        return $('#shipping_address_city_ref').val() || '';
    }

    function hasCity() {
        return !!$.trim(cityInputs().first().val() || '') || !!$('#shipping_address_city_ref').val();
    }

    function setAddrLock($select) {
        var instance = $select.data('select2');
        var $container = instance && instance.$container ? instance.$container : $select.next('.select2-container');

        if (!$container || !$container.length) {
            return;
        }

        if (hasCity()) {
            $container.removeClass('np-locked').removeAttr('title');
        } else {
            $container.addClass('np-locked').attr('title', 'Спочатку оберіть населений пункт');
        }
    }

    function refreshAddrLock() {
        addrInputs().each(function () {
            var $widget = $(this).data('np-widget');

            if ($widget) {
                setAddrLock($widget);
            }
        });
    }

    function ajaxOptions(getAction, getFilter) {
        return {
            url: 'index.php?route=extension/module/shippingdata/getShippingData',
            type: 'POST',
            dataType: 'json',
            delay: 250,
            cache: false,
            data: function (params) {
                return {
                    shipping: shippingData.method,
                    action: getAction(),
                    filter: getFilter(),
                    search: params.term || ''
                };
            },
            processResults: function (json) {
                return {
                    results: $.map(json, function (item) {
                        return { id: item.description, text: item.description, ref: item.id };
                    })
                };
            }
        };
    }

    function buildSelect($input) {
        var $select = $('<select class="np-select2"></select>');
        var current = $.trim($input.val() || '');

        $input.after($select).addClass('np-hidden-input').hide();

        $select.append($('<option></option>').val('').text(''));
        if (current) {
            $select.append($('<option></option>').val(current).text(current).prop('selected', true));
        }

        return $select;
    }

    function initCity() {
        cityInputs().each(function () {
            var $input = $(this);

            if ($input.data('np-widget')) {
                return;
            }

            var $select = buildSelect($input);

            $select.select2({
                width: '100%',
                //allowClear: true,
                placeholder: window.cityPlaceholder,
                minimumInputLength: 0,
                ajax: ajaxOptions(function () { return 'getCities'; }, cityFilter)
            });

            $select.on('select2:select', function (e) {
                $input.val(e.params.data.text).trigger('change');
                $(this).data('ref', e.params.data.ref);
                $('#shipping_address_city_ref').val(e.params.data.ref);
                clearAddress();
            });

            $select.on('select2:clear', function () {
                $input.val('').trigger('change');
                $(this).removeData('ref');
                $('#shipping_address_city_ref').val('');
                clearAddress();
            });

            $input.data('np-widget', $select);
        });
    }

    function initAddress(force) {
        addrInputs().each(function () {
            var $input = $(this);
            var $old = $input.data('np-widget');

            if ($old) {
                if (!force && $input.data('np-method') === shippingData.method) {
                    return;
                }

                if ($old.hasClass('select2-hidden-accessible')) {
                    $old.select2('destroy');
                }

                $old.remove();
            }

            var $select = buildSelect($input);
            $input.data('np-method', shippingData.method);

            $select.select2({
                width: '100%',
                //allowClear: true,
                placeholder: window.departmentPlaceholder,
                minimumInputLength: 0,
                ajax: ajaxOptions(addrAction, addrFilter)
            });

            setAddrLock($select);

            $select.on('select2:opening', function (e) {
                if (!hasCity()) {
                    e.preventDefault();
                }
            });

            $select.on('select2:select', function (e) {
                $input.val(e.params.data.text).trigger('change');
                $('#shipping_address_address_ref').val(e.params.data.ref);
            });

            $select.on('select2:clear', function () {
                $input.val('').trigger('change');
                $('#shipping_address_address_ref').val('');
            });

            $input.data('np-widget', $select);
        });
    }

    function resetWidgets(inputsGet, refSelector) {
        inputsGet().each(function () {
            var $input = $(this);
            var $widget = $input.data('np-widget');

            $input.val('');

            if ($widget) {
                if ($widget.hasClass('select2-hidden-accessible')) {
                    $widget.val(null).trigger('change.select2');
                } else {
                    $widget.val(null);
                }
            }
        });

        $(refSelector).val('');
    }

    function clearCity() {
        resetWidgets(cityInputs, '#shipping_address_city_ref');
    }

    function clearAddress() {
        resetWidgets(addrInputs, '#shipping_address_address_ref');
        refreshAddrLock();
    }

    function initNpSelects(force) {
        shippingData.setProp();

        if (!shippingData.isActive()) {
            return;
        }

        $('.simplecheckout-block-content').css('overflow', 'visible');

        initCity();
        initAddress(force);
    }

    $(function () {
        initNpSelects(false);
        window.setTimeout(function () { initNpSelects(false); }, 300);

        $(document).ajaxStop(function () {
            initNpSelects(false);
        });

        $(document).on('change', '[name*="zone"]:visible', function () {
            clearCity();
            clearAddress();
        });

        $(document).on('change', '[name*="shipping_method"]', function () {
            shippingData.setProp();
            clearCity();
            clearAddress();
            initNpSelects(true);
        });
    });
})(window.jQuery);

