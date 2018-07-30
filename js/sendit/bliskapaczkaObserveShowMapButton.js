function bliskapaczkaObserveShowMapButton() {

}

bliskapaczkaObserveShowMapButton.init = function () {
    this.showMapButtonAttributeName = 'data-title';
    this.showMapButtonAttributeValue = 'show_map';
    bliskapaczkaObserveShowMapButton.observe();
}

bliskapaczkaObserveShowMapButton.observe = function () {
    Event.observe(document, 'click', function (e) {
        if ($(e.element()).getAttribute(bliskapaczkaObserveShowMapButton.showMapButtonAttributeName) == bliskapaczkaObserveShowMapButton.showMapButtonAttributeValue) {
            var checkboxElement = bliskapaczkaObserveShowMapButton.getCheckboxElementByShowMapElement(e.element());
            checkboxElement.click();
        }
    });
}

bliskapaczkaObserveShowMapButton.getCheckboxElementByShowMapElement = function(element) {
    var deliveryContainer = element.parentNode;
    deliveryContainer = deliveryContainer.parentNode;

    return deliveryContainer.querySelector('input.radio');
}

bliskapaczkaObserveShowMapButton.init();