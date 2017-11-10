function Bliskapaczka()
{
}
 
Bliskapaczka.showMap = function(operators, googleMapApiKey, testMode)
{
    aboutPoint = document.getElementById('bpWidget_aboutPoint');
    aboutPoint.style.display = 'none';

    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'block';

    BPWidget.init(
        bpWidget,
        {
            googleMapApiKey: googleMapApiKey,
            callback: function(data) {
                posCodeForm = document.getElementsByName('bliskapaczka[posCode]')[0]
                posOperatorForm = document.getElementsByName('bliskapaczka[posOperator]')[0]

                posCodeForm.value = data.code;
                posOperatorForm.value = data.operator;

                Bliskapaczka.pointSelected(data, operators);
            },
            operators: operators,
            posType: 'DELIVERY',
            testMode: testMode
        }
    );
}

Bliskapaczka.pointSelected = function(data, operators)
{
    Bliskapaczka.updatePrice(data.operator, operators);

    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'none';

    aboutPoint = document.getElementById('bpWidget_aboutPoint');
    aboutPoint.style.display = 'block';

    posDataBlock = document.getElementById('bpWidget_aboutPoint_posData');

    posDataBlock.innerHTML =  data.operator + '</br>'
        + ((data.description) ? data.description + '</br>': '')
        + data.street + '</br>'
        + ((data.postalCode) ? data.postalCode + ' ': '') + data.city
}

Bliskapaczka.updatePrice = function (posOperator, operators) {
    boxSpan = document.getElementsByClassName('bliskapaczka_price_box')[0];
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
