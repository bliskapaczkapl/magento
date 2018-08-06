var Point = Class.create();
Point.prototype = {
    initialize: function () {
        this.code = null;
        this.operator = null;
    },
    setCode: function (code) {
        this.code = code;
    },
    setOperator: function (operator) {
        this.operator = operator;
    },
    getObject: function () {
        return {"code": this.code, "operator": this.operator}
    }

}

pointSelected = new Point();
function Bliskapaczka()
{
}
 
Bliskapaczka.showMap = function(operators, googleMapApiKey, testMode, rateCode, codOnly = false)
{
    aboutPoint = document.getElementById('bpWidget_aboutPoint_' + rateCode);
    aboutPoint.style.display = 'none';

    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'block';

    BPWidget.init(
        bpWidget,
        {
            googleMapApiKey: googleMapApiKey,
            callback: function(data) {
                pointSelected.setCode(data.code);
                pointSelected.setOperator(data.operator);
                posCodeForm = document.getElementById('s_method_' + rateCode + '_posCode');
                posOperatorForm = document.getElementById('s_method_' + rateCode + '_posOperator');

                posCodeForm.value = data.code;
                posOperatorForm.value = data.operator;

                Bliskapaczka.pointSelected(data, operators, rateCode);
            },
            operators: operators,
            posType: 'DELIVERY',
            testMode: testMode,
            codOnly: codOnly,
            selectedPos: pointSelected.getObject()
        }
    );
}

Bliskapaczka.pointSelected = function(data, operators, rateCode)
{
    Bliskapaczka.updatePrice(data.operator, operators, rateCode);

    bpWidget = document.getElementById('bpWidget');
    bpWidget.style.display = 'none';

    aboutPoint = document.getElementById('bpWidget_aboutPoint_' + rateCode);
    aboutPoint.style.display = 'block';

    posDataBlock = document.getElementById('bpWidget_aboutPoint_posData_' + rateCode);
    posCodeDescriptionForm = document.getElementById('s_method_' + rateCode + '_posCodeDescription')

    description = data.operator + '</br>'
        + ((data.description) ? data.description + '</br>': '')
        + data.street + '</br>'
        + ((data.postalCode) ? data.postalCode + ' ': '') + data.city

    posDataBlock.innerHTML = description;
    posCodeDescriptionForm.value = description;
}

Bliskapaczka.updatePrice = function (posOperator, operators, rateCode) {
    boxSpan = document.getElementsByClassName('bliskapaczka_price_box_' + rateCode)[0];
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
