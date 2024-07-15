(function() {
	'use strict';

	if (!!window.JCSaleProductsGiftBasketComponent)
		return;

	window.JCSaleProductsGiftBasketComponent = function(params) {
		
		this.formPosting = false;
		this.siteId = params.siteId || '';
		this.template = params.template || '';
		this.componentPath = params.componentPath || '';
		this.parameters = params.parameters || '';
		this.customParams = params.customParams || '';

		this.container = document.querySelector('[data-entity="' + params.container + '"]');
		this.currentProductId = params.currentProductId;

		if (params.initiallyShowHeader)
		{
			BX.ready(BX.proxy(this.showHeader, this));
		}

		if (params.deferredLoad)
		{
			BX.ready(BX.proxy(this.deferredLoad, this));
		}

		BX.addCustomEvent('OnBasketChange', BX.proxy(this.reloadGifts, this));
		BX.addCustomEvent('OnCouponApply', BX.proxy(this.reloadGifts, this));
	};

	window.JCSaleProductsGiftBasketComponent.prototype =
	{
		reloadGifts: function()
		{
			this.sendRequest({action: 'deferredLoad', recalculateDiscounts: 'Y'});

		},

		deferredLoad: function()
		{
			this.sendRequest({action: 'deferredLoad'});
		},

		sendRequest: function(data)
		{
			var defaultData = {
				siteId: this.siteId,
				template: this.template,
				parameters: this.parameters
			};

			BX.ajax({
				url: this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
				method: 'POST',
				dataType: 'json',
				timeout: 60,
				data: BX.merge(defaultData, data),
				onsuccess: BX.delegate(function(result){
					BX.remove(BX('custom-loader'));
					BX.remove(BX('custom-loader_bg'));
					if (!result || !result.JS)
					{
						this.hideHeader();
						BX.cleanNode(this.container);
						return;
					}

					BX.ajax.processScripts(
						BX.processHTML(result.JS).SCRIPT,
						false,
						BX.delegate(function(){this.showAction(result, data);}, this)
					);
				}, this)
			});
		},

		showAction: function(result, data)
		{
			if (!data)
				return;

			switch (data.action)
			{
				case 'deferredLoad':
					this.processDeferredLoadAction(result);
					break;
			}
		},

		processDeferredLoadAction: function(result)
		{
			if (!result)
				return;

			this.processItems(result.items);
		},

		processItems: function(itemsHtml)
		{
			if (!itemsHtml)
				return;
			var processed = BX.processHTML(itemsHtml, false),
				temporaryNode = BX.create('DIV');

			var items, k, origRows, basketItems, basketIds = {};

			temporaryNode.innerHTML = processed.HTML;

			origRows = this.container.querySelectorAll('[data-entity="items-row"]');
			if (origRows.length)
			{
				BX.cleanNode(this.container);
				this.showHeader(false);
			}
			else
			{
				this.showHeader(true);
			}

			items = temporaryNode.querySelectorAll('[data-entity="items-row"]');
			basketItems = document.querySelectorAll('.basket-items-list-item-container');

			if(basketItems.length > 0)
			{
				for(var i = 0; i < basketItems.length; i++)
				{

					basketIds[basketItems[i].dataset.product] = '';
				}
			}
			for (k in items)
			{
				if (items.hasOwnProperty(k))
				{
					let products = items[k].querySelectorAll('.product-item-container');
					let d = 0;
					if(products.length > 0){
						for(var j = 0; j < products.length; j++){

							let item = products[j];
							let productId = item.id;
							let splits = productId.split("_");
							if(basketIds[splits[2]] != null){
								item.parentNode.style.display = 'none';
								d++;
							}
							let price = items[k].querySelector('#'+productId+'_price');
							let priceOld = items[k].querySelector('#'+productId+'_price_old');
							let discount = items[k].querySelector('#'+productId+'_dsc_perc span');
							let sticker = items[k].querySelector('#'+productId+'_sticker span');
							if(price && (this.stringToNumber(price.innerText) == 0 || this.stringToNumber(price.innerText) == '0 руб.')){
								let priceOldInt = this.stringToNumber(priceOld.innerText);
								let newDiscount = ((priceOldInt-10)*100)/priceOldInt;
								discount.innerText = '-' + parseInt(newDiscount) + '%';
								price.innerText  = '10 руб.';
								sticker.innerText = 'Акция';
							}

						}

						if(products.length == d){
							document.querySelector('[data-entity="sale-products-gift-container"]').parentNode.style.display = 'none';
						}
					}
					
					items[k].style.opacity = 0;
					this.container.appendChild(items[k]);
				}
			
			}

			new BX.easing({
				duration: 2000,
				start: {opacity: 0},
				finish: {opacity: 100},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function(state){
					for (var k in items)
					{
						if (items.hasOwnProperty(k))
						{
							items[k].style.opacity = state.opacity / 100;
						}
					}
				},
				complete: function(){
					for (var k in items)
					{
						if (items.hasOwnProperty(k))
						{
							items[k].removeAttribute('style');
						}
					}
				}
			}).animate();


			if (processed.SCRIPT) {
			    BX.ajax.processScripts(processed.SCRIPT);
			}

			if (BX('custom__orderPriceValue') && BX.Sale.BasketComponent.giftPriceOrder) {
				BX('custom__orderPriceValue').innerText = 'Добавьте в заказ еще товаров на ' + BX.Sale.BasketComponent.giftPriceOrder.value + ' рублей, чтобы получить подарок:';
				BX('custom__orderPriceValue').style.display = !!BX.Sale.BasketComponent.giftPriceOrder.display ? 'block' : 'none';
			}
		},

		stringToNumber: function(str)
		{
			str = str.trim();
			str = str.replace(" ", "");
			str = str.replace(/\s/, "");
			str = str.replace("&nbsp;", "");
			str = str.replace("руб.", "");
			return parseInt(str);
		},

		showHeader: function(animate)
		{
			var parentNode = BX.findParent(this.container, {attr: {'data-entity': 'parent-container'}}),
				header;

			if (parentNode && BX.type.isDomNode(parentNode))
			{
				header = parentNode.querySelector('[data-entity="header"]');

				if (header && header.getAttribute('data-showed') === 'false')
				{
					header.style.display = '';

					if (animate)
					{
						this.animation = new BX.easing({
							duration: 2000,
							start: {opacity: 0},
							finish: {opacity: 100},
							transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
							step: function(state){
								header.style.opacity = state.opacity / 100;
							},
							complete: function(){
								header.removeAttribute('style');
								header.setAttribute('data-showed', 'true');
							}
						});
						this.animation.animate()
					}
					else
					{
						header.style.opacity = 100;
					}
				}
			}
		},

		hideHeader: function()
		{
			var parentNode = BX.findParent(this.container, {attr: {'data-entity': 'parent-container'}}),
				header;

			if (parentNode && BX.type.isDomNode(parentNode))
			{
				header = parentNode.querySelector('[data-entity="header"]');

				if (header)
				{
					if (this.animation)
					{
						this.animation.stop();
					}

					header.style.display = 'none';
					header.style.opacity = 0;
					header.setAttribute('data-showed', 'false');
				}
			}
		}
	}

})();