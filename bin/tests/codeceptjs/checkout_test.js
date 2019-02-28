Feature('Checkout');

Scenario('test something', (I) => {
    I.amOnPage('/fusion-backpack.html');
    I.waitForResponse(request => request.url().includes('/review/product/listAjax'));
    I.click({id: 'product-addtocart-button'});
    I.retry(5).see('You added Fusion Backpack to your shopping cart.');
    I.amOnPage('/checkout');
});

After(pause);
