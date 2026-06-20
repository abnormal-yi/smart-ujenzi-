document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('region_id');
    const districtSelect = document.getElementById('district_id');
    const wardSelect = document.getElementById('ward_id');

    if (!regionSelect) return;

    var ts = Date.now();

    function setOptions(select, options, placeholder) {
        select.innerHTML = '<option value="">' + placeholder + '</option>';
        options.forEach(function(opt) {
            var el = document.createElement('option');
            el.value = (opt.id != null ? opt.id : opt.value) || opt.name;
            el.textContent = opt.name;
            el.className = 'bg-gray-800 text-white';
            select.appendChild(el);
        });
    }

    // Load regions
    fetch('/api/location.php?action=regions&_=' + ts)
        .then(function(r) { return r.json(); })
        .then(function(regions) {
            setOptions(regionSelect, regions, 'Select Region');
        })
        .catch(function() {});

    // Load districts on region change
    regionSelect.addEventListener('change', function() {
        districtSelect.innerHTML = '<option value="">Loading...</option>';
        districtSelect.disabled = true;
        if (wardSelect) {
            wardSelect.innerHTML = '<option value="">Select Ward</option>';
            wardSelect.disabled = true;
        }

        if (!this.value) {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            return;
        }

        fetch('/api/location.php?action=districts&region_id=' + this.value + '&_=' + ts)
            .then(function(r) { return r.json(); })
            .then(function(districts) {
                setOptions(districtSelect, districts, 'Select District');
                districtSelect.disabled = false;
            })
            .catch(function() {});
    });

    // Load wards on district change
    if (districtSelect && wardSelect) {
        districtSelect.addEventListener('change', function() {
            wardSelect.innerHTML = '<option value="">Loading...</option>';
            wardSelect.disabled = true;

            if (!this.value) {
                wardSelect.innerHTML = '<option value="">Select Ward</option>';
                return;
            }

            fetch('/api/location.php?action=wards&district_id=' + this.value + '&_=' + Date.now())
                .then(function(r) { return r.json(); })
                .then(function(wards) {
                    setOptions(wardSelect, wards, 'Select Ward');
                    wardSelect.disabled = false;
                })
                .catch(function() {});
        });
    }
});