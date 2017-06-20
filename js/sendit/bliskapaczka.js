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
            callback: function(posCode, posOperator) {
                console.log('BPWidget callback:', posCode, posOperator)

                posCodeForm = document.getElementsByName('bliskapaczka[posCode]')[0]
                posOperatorForm = document.getElementsByName('bliskapaczka[posOperator]')[0]

                posCodeForm.value = posCode;
                posOperatorForm.value = posOperator;

                Bliskapaczka.pointSelected(posCode, posOperator);
            },
            prices: prices,
            disabledOperators: disabledOperators,
            posType: 'DELIVERY'
        }
    );
}

Bliskapaczka.pointSelected = function(posCode, posOperator)
{
    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'none';

    aboutPoint = document.getElementById('bpWidget_aboutPoint');
    aboutPoint.style.display = 'block';

    posCodeBlock = document.getElementById('bpWidget_aboutPoint_posCode');
    posOperatorBlock = document.getElementById('bpWidget_aboutPoint_posOperator');

    posCodeBlock.innerHTML = posCode
    posOperatorBlock.innerHTML = posOperator
}
