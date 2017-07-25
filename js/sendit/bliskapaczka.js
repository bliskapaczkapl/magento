function Bliskapaczka()
{
}
 
Bliskapaczka.showMap = function(prices, disabledOperators)
{
    aboutPoint = document.getElementById('bpWidget_aboutPoint');
    aboutPoint.style.display = 'none';

    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'block';

    BPWidget.init(
        bpWidget,
        {
            callback: function(data) {
                console.log('BPWidget callback:', data.code, data.operator)

                posCodeForm = document.getElementsByName('bliskapaczka[posCode]')[0]
                posOperatorForm = document.getElementsByName('bliskapaczka[posOperator]')[0]

                posCodeForm.value = data.code;
                posOperatorForm.value = data.operator;

                Bliskapaczka.pointSelected(data.code, data.operator, prices);
            },
            prices: prices,
            disabledOperators: disabledOperators,
            posType: 'DELIVERY'
        }
    );
}

Bliskapaczka.pointSelected = function(posCode, posOperator, prices)
{
    Bliskapaczka.updatePrice(posOperator, prices);

    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'none';

    aboutPoint = document.getElementById('bpWidget_aboutPoint');
    aboutPoint.style.display = 'block';

    posCodeBlock = document.getElementById('bpWidget_aboutPoint_posCode');
    posOperatorBlock = document.getElementById('bpWidget_aboutPoint_posOperator');

    posCodeBlock.innerHTML = posCode
    posOperatorBlock.innerHTML = posOperator
}

Bliskapaczka.updatePrice = function (posOperator, prices) {
    console.log('zupa')

    boxSpan = document.getElementsByClassName('bliskapaczka_price_box')[0];
    priceSpan = boxSpan.getElementsByClassName('price')[0];

    console.log('sdasdsa')
    console.log(priceSpan);

    price = prices[posOperator];
    priceSpan.innerHTML = priceSpan.innerHTML.replace(/([\d\.,]{2,})/, price);
}
