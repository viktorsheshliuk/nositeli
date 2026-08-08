;(function($) {
	'use strict';

	var POPUP_URL = 'index.php?route=common/popup_cart';
	var lastProductId = 0;

	/* ======= Открытие / закрытие панели ======= */
	function openPopupCart() {
		$('#fn_pop_up_cart_wrap').addClass('open');
		$('body').addClass('popup-cart-open');
	}

	function closePopupCart() {
		$('#fn_pop_up_cart_wrap').removeClass('open');
		$('body').removeClass('popup-cart-open');
	}

	/* Обновление счётчика и суммы в шапке */
	function updateHeaderCart() {
		$('#cart').load('index.php?route=common/cart/info #cart > *');
	}

	/* Перерисовка содержимого панели (только последний добавленный товар) */
	function refreshPopup() {
		var url = POPUP_URL;

		if (lastProductId) {
			url += '&product_id=' + lastProductId;
		}

		$('#fn_pop_up_cart').load(url, function() {
			// Если корзина пуста — закрываем панель
			if ($('#fn_pop_up_cart').find('.popup_purchase').length === 0) {
				closePopupCart();
			}
		});
	}

	function loadAndOpenPopup() {
		var url = POPUP_URL;

		if (lastProductId) {
			url += '&product_id=' + lastProductId;
		}

		$('#fn_pop_up_cart').load(url, function() {
			openPopupCart();
		});
	}

	/* ======= Переопределяем глобальный объект cart ======= */
	var cart = {
		add: function(product_id, quantity) {
			var data;
			var $productForm = $('#product');

			// Страница товара — собираем все поля (опции, количество)
			if ($productForm.find('input[name="product_id"]').length) {
				data = $productForm.find(
					'input[type="text"], input[type="hidden"], input[type="radio"]:checked, input[type="checkbox"]:checked, select, textarea'
				).serialize();

				// Запоминаем последний добавленный товар для панели
				lastProductId = parseInt($productForm.find('input[name="product_id"]').val(), 10) || 0;
			} else {
				// Каталог / производитель / поиск / похожие
				quantity = typeof quantity !== 'undefined' ? quantity : 1;

				var $options = $('#option_' + product_id + ' input[type="text"], #option_' + product_id + ' input[type="radio"]:checked, #option_' + product_id + ' input[type="checkbox"]:checked, #option_' + product_id + ' select, #option_' + product_id + ' textarea');

				if ($options.length) {
					data = $options.serialize() + '&product_id=' + product_id + '&quantity=' + quantity;
				} else {
					data = 'product_id=' + product_id + '&quantity=' + quantity;
				}

				// Запоминаем последний добавленный товар для панели
				lastProductId = parseInt(product_id, 10) || 0;
			}

			$.ajax({
				url: 'index.php?route=checkout/cart/add',
				type: 'post',
				data: data,
				dataType: 'json',
				beforeSend: function() {
					$('#button-cart').button('loading');
				},
				complete: function() {
					$('#button-cart').button('reset');
				},
				success: function(json) {
					$('.alert-dismissible, .text-danger').remove();
					$('.form-group').removeClass('has-error');

					if (json['redirect']) {
						location = json['redirect'];
						return;
					}

					if (json['error']) {
						if (json['error']['option']) {
							for (var i in json['error']['option']) {
								var element = $('#input-option' + i.replace('_', '-'));

								if (element.parent().hasClass('input-group')) {
									element.parent().after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
								} else {
									element.after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
								}
							}
						}

						if (json['error']['recurring']) {
							$('select[name="recurring_id"]').after('<div class="text-danger">' + json['error']['recurring'] + '</div>');
						}

						$('.text-danger').parent().addClass('has-error');
						return;
					}

					if (json['success']) {
						updateHeaderCart();
						loadAndOpenPopup();
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		},

		update: function(key, quantity) {
			if (typeof quantity === 'undefined' || quantity === null) {
				quantity = 1;
			}

			// Используем свой update (в checkout/cart/edit — полностраничный redirect)
			$.ajax({
				url: 'index.php?route=common/popup_cart/update',
				type: 'post',
				data: { key: key, quantity: quantity },
				dataType: 'json',
				success: function(json) {
					if (!json['success']) {
						return;
					}

					updateHeaderCart();
					refreshPopup();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		},

		remove: function(key) {
			$.ajax({
				url: 'index.php?route=checkout/cart/remove',
				type: 'post',
				data: { key: key },
				dataType: 'json',
				success: function(json) {
					updateHeaderCart();
					refreshPopup();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	};

	window.cart = cart;

	/* ======= Изменение количества внутри панели ======= */
	function changeQty($btn, delta) {
		var $amount = $btn.closest('.amount');
		var $input = $amount.find('input');

		if (!$input.length) {
			return;
		}

		var current = parseInt($input.val(), 10) || 0;
		var min = parseInt($input.data('min') || 1, 10);
		var next = current + delta;

		if (next < min) {
			next = min;
		}

		$input.val(next);
		cart.update($input.data('cart-id'), next);
	}

	/* ======= Обработчики панели (делегирование) ======= */
	$(document).on('click', '#fn_pop_up_cart_wrap .btn_close_popup', function(e) {
		e.preventDefault();
		closePopupCart();
	});

	$(document).on('click', '#fn_pop_up_cart_wrap .close_popup_bg', function(e) {
		e.preventDefault();
		closePopupCart();
	});

	$(document).on('click', '#fn_pop_up_cart_wrap .form__button_continue', function(e) {
		e.preventDefault();
		closePopupCart();
	});

	// Кнопки +/− перехватываем в capture-фазе, чтобы глобальные
	// обработчики .minus/.plus из click.min.js не меняли количество дважды
	document.addEventListener('click', function(e) {
		var minusEl = e.target.closest ? e.target.closest('#fn_pop_up_cart_wrap .minus') : null;
		var plusEl = e.target.closest ? e.target.closest('#fn_pop_up_cart_wrap .plus') : null;

		if (minusEl || plusEl) {
			e.preventDefault();
			e.stopPropagation();

			if (minusEl) {
				changeQty($(minusEl), -1);
			} else {
				changeQty($(plusEl), 1);
			}
		}
	}, true);

	// Закрытие по Escape
	$(document).on('keydown', function(e) {
		if (e.keyCode === 27) {
			closePopupCart();
		} 
	});

	/* ======= Перехват кнопки товара на странице продукта ======= */
	$(document).ready(function() {
		if ($('#button-cart').length) {
			$('#button-cart').off('click');
			$('#button-cart').on('click', function(e) {
				e.preventDefault();
				cart.add();
			});
		}
	});

})(jQuery);