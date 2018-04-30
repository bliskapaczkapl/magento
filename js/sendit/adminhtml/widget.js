document.write("<script src='https://widget.bliskapaczka.pl/v5/main.js'><\/script>");

AdminOrder.prototype.setShippingMethod = function(method) {
    var data = {};
    data['order[shipping_method]'] = method;

    posCodeForm = document.getElementsByName('bliskapaczka[posCode]')[0]
    posOperatorForm = document.getElementsByName('bliskapaczka[posOperator]')[0]

    data['bliskapaczka[posCode]'] = posCodeForm.value;
    data['bliskapaczka[posOperator]'] = posOperatorForm.value;

    this.loadArea(['shipping_method', 'totals', 'billing_method'], true, data);
};
