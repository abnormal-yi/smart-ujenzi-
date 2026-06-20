document.addEventListener('DOMContentLoaded', function() {
    var regionSel = document.getElementById('region_id');
    var districtSel = document.getElementById('district_id');
    var wardSel = document.getElementById('ward_id');
    if (!regionSel) return;

    var regionToDistrict = TZ_DISTRICTS || {};
    var wardData = TZ_WARDS || {};

    function populate(sel, items, placeholder, valKey) {
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        items.forEach(function(item) {
            var el = document.createElement('option');
            el.value = valKey ? item[valKey] : item;
            el.textContent = item.name || item;
            sel.appendChild(el);
        });
    }

    populate(regionSel, (TZ_REGIONS || []).map(function(n) { return { name: n }; }), 'Select Region', 'name');

    regionSel.addEventListener('change', function() {
        districtSel.innerHTML = '<option value="">Select District</option>';
        districtSel.disabled = !this.value;
        if (wardSel) {
            wardSel.innerHTML = '<option value="">Select Ward</option>';
            wardSel.disabled = true;
        }
        var regionName = this.value;
        if (!regionName) return;
        var dists = [];
        for (var d in regionToDistrict) {
            if (regionToDistrict[d] === regionName) {
                dists.push({ name: d });
            }
        }
        dists.sort(function(a, b) { return a.name.localeCompare(b.name); });
        populate(districtSel, dists, 'Select District', 'name');
    });

    if (districtSel && wardSel) {
        districtSel.addEventListener('change', function() {
            wardSel.innerHTML = '<option value="">Select Ward</option>';
            wardSel.disabled = !this.value;
            var distName = this.value;
            if (!distName) return;
            var wards = (wardData[distName] || []).map(function(w) { return { name: w }; });
            populate(wardSel, wards, 'Select Ward', 'name');
        });
    }
});
