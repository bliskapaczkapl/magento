function Bliskapaczka()
{
}
 
Bliskapaczka.showMap = function(operators, googleMapApiKey, testMode, rateCode, codOnly = false)
{
    aboutPoint = document.getElementById('bpWidget_aboutPoint_' + rateCode);
    aboutPoint.style.display = 'none';

    bpWidget = document.getElementById('bpWidget_' + rateCode);
    bpWidget.style.display = 'block';

    BPWidget.init(
        bpWidget,
        {
            googleMapApiKey: googleMapApiKey,
            callback: function(data) {
                posCodeForm = document.getElementById('s_method_' + rateCode + '_posCode');
                posOperatorForm = document.getElementById('s_method_' + rateCode + '_posOperator');
                posCodeDescriptionForm = document.getElementsByName('s_method_' + rateCode + '_posOperator')

                posCodeForm.value = data.code;

                posOperatorForm.value = data.operator;

                function setPosCodeDescription(element, index) {
                    element.value = data.operator + '</br>'
                    + ((data.description) ? data.description + '</br>': '')
                    + data.street + '</br>'
                    + ((data.postalCode) ? data.postalCode + ' ': '') + data.city
                }
                posCodeDescriptionForm.forEach(setPosCodeDescription);

                Bliskapaczka.pointSelected(data, operators);
            },
            operators: operators,
            posType: 'DELIVERY',
            testMode: testMode,
            codOnly: codOnly
        }
    );
}

Bliskapaczka.pointSelected = function(data, operators, rateCode)
{
    Bliskapaczka.updatePrice(data.operator, operators);

    bpWidget = document.getElementById('bpWidget_' + rateCode);
    bpWidget.style.display = 'none';

    aboutPoint = document.getElementById('bpWidget_aboutPoint_' + rateCode);
    aboutPoint.style.display = 'block';

    posDataBlock = document.getElementById('bpWidget_aboutPoint_posData_' + rateCode);

    posDataBlock.innerHTML =  data.operator + '</br>'
        + ((data.description) ? data.description + '</br>': '')
        + data.street + '</br>'
        + ((data.postalCode) ? data.postalCode + ' ': '') + data.city
}

Bliskapaczka.updatePrice = function (posOperator, operators) {
    boxSpan = document.getElementsByClassName('bliskapaczka_price_box')[0];
    if (boxSpan) {
        if (boxSpan.getElementsByClassName('price')) {
            priceSpan = boxSpan.getElementsByClassName('price')[0];

            for (var i = 0; i < operators.length; i++) {
                if (operators[i].operator == posOperator) {
                    price = operators[i].price;
                }
            }

            priceSpan.innerHTML = priceSpan.innerHTML.replace(/([\d\.,]{2,})/, price);
            // Remove word "From"
            boxSpan.innerHTML = '';
            boxSpan.appendChild(priceSpan)
        }
    }

}
