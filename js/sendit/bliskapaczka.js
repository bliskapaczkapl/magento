function Bliskapaczka()
{
}
 
Bliskapaczka.showMap = function(operators, googleMapApiKey)
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
                console.log(data)
                console.log('BPWidget callback:', data.code, data.operator)

                posCodeForm = document.getElementsByName('bliskapaczka[posCode]')[0]
                posOperatorForm = document.getElementsByName('bliskapaczka[posOperator]')[0]

                posCodeForm.value = data.code;
                posOperatorForm.value = data.operator;

                Bliskapaczka.pointSelected(data.code, data.operator, operators);
            },
            operators: operators,
            posType: 'DELIVERY'
        }
    );
}

Bliskapaczka.pointSelected = function(posCode, posOperator, operators)
{
    Bliskapaczka.updatePrice(posOperator, operators);

    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'none';

    aboutPoint = document.getElementById('bpWidget_aboutPoint');
    aboutPoint.style.display = 'block';

    posCodeBlock = document.getElementById('bpWidget_aboutPoint_posCode');
    posOperatorBlock = document.getElementById('bpWidget_aboutPoint_posOperator');

    posCodeBlock.innerHTML = posCode
    posOperatorBlock.innerHTML = posOperator
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
}
