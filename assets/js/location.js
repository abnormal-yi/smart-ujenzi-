document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('region_id');
    const districtSelect = document.getElementById('district_id');

    if (!regionSelect) return;

    // Load regions
    fetch('/api/location.php?action=regions')
        .then(r => r.json())
        .then(regions => {
            regionSelect.innerHTML = '<option value="">Select Region</option>';
            regions.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.name;
                regionSelect.appendChild(opt);
            });
        });

    // Load districts on region change
    regionSelect.addEventListener('change', function() {
        districtSelect.innerHTML = '<option value="">Loading...</option>';
        districtSelect.disabled = true;

        if (!this.value) {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            return;
        }

        fetch('/api/location.php?action=districts&region_id=' + this.value)
            .then(r => r.json())
            .then(districts => {
                districtSelect.innerHTML = '<option value="">Select District</option>';
                districts.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d.name;
                    opt.textContent = d.name;
                    districtSelect.appendChild(opt);
                });
                districtSelect.disabled = false;
            });
    });
});
