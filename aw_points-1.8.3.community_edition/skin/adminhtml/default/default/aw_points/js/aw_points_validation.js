Validation.add('aw-points-validate-percent', 'Please use value from 0 to 100 (%) in this field.', function(v) {
    if (Validation.get('IsEmpty').test(v)) {
        return true;
    }
    var numValue = parseNumber(v);
    if (isNaN(numValue)) {
        return false;
    }
    return (numValue >= 0) && (numValue <= 100);
});


