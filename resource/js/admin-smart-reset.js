(function () {
    'use strict';
    var form = document.querySelector('.smart-reset-console');
    if (!form) return;
    var config = window.aihlSmartReset || {};
    var checks = Array.from(form.querySelectorAll('input[name="components[]"]'));
    var factory = form.querySelector('[data-reset-factory]');
    var confirmation = form.querySelector('.smart-reset-confirm input[type="text"]');
    var submit = form.querySelector('.smart-reset-submit');
    var count = document.getElementById('smart-reset-count');
    var summary = document.getElementById('smart-reset-summary');
    function update() {
        if (factory && factory.checked) {
            checks.filter(function (item) { return item !== factory; }).forEach(function (item) {
                item.checked = false;
                item.disabled = true;
                item.closest('.smart-reset-row').classList.add('is-disabled');
            });
        } else {
            checks.forEach(function (item) {
                item.disabled = false;
                item.closest('.smart-reset-row').classList.remove('is-disabled');
            });
        }
        var selected = checks.filter(function (item) { return item.checked; });
        var template = selected.length === 1 ? config.selectedSingular : config.selectedPlural;
        count.textContent = (template || '%d selezionati').replace('%d', selected.length);
        summary.textContent = selected.length ? selected.map(function (item) {
            return item.closest('.smart-reset-row').querySelector('strong').textContent;
        }).join(', ') : config.emptySummary;
        submit.disabled = !selected.length || confirmation.value.trim().toUpperCase() !== 'RESET';
    }
    checks.forEach(function (item) { item.addEventListener('change', update); });
    confirmation.addEventListener('input', update);
    form.addEventListener('submit', function (event) {
        if (submit.disabled || !window.confirm(config.confirmMessage)) event.preventDefault();
    });
    update();
}());
