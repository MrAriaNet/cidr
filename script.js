document.addEventListener('DOMContentLoaded', function() {
    const ipInput = document.getElementById('ip-address');
    const cidrSelect = document.getElementById('cidr-select');
    const calculateBtn = document.getElementById('calculate-btn');
    const resultsDiv = document.getElementById('results');
    const ipv4Results = document.getElementById('ipv4-results');
    const ipv6Results = document.getElementById('ipv6-results');
    const errorMessage = document.getElementById('error-message');

    // Populate CIDR dropdown
    function populateCIDRSelect() {
        // IPv4 CIDR (0-32)
        for (let i = 0; i <= 32; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = '/' + i;
            cidrSelect.appendChild(option);
        }
    }

    populateCIDRSelect();

    // Update CIDR select when input changes
    ipInput.addEventListener('input', function() {
        const value = this.value;
        if (value.includes('/')) {
            const parts = value.split('/');
            if (parts.length === 2) {
                const cidr = parseInt(parts[1]);
                if (!isNaN(cidr)) {
                    cidrSelect.value = cidr;
                }
            }
        }
    });

    // Update input when CIDR select changes
    cidrSelect.addEventListener('change', function() {
        const value = ipInput.value;
        if (value && !value.includes('/')) {
            ipInput.value = value + '/' + this.value;
        } else if (value.includes('/')) {
            const parts = value.split('/');
            ipInput.value = parts[0] + '/' + this.value;
        }
    });

    // Handle Enter key
    ipInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            calculateBtn.click();
        }
    });

    // Calculate button click
    calculateBtn.addEventListener('click', function() {
        const input = ipInput.value.trim();
        
        if (!input) {
            showError('Please enter an IP address with CIDR notation');
            return;
        }

        // Show loading state
        calculateBtn.textContent = 'Calculating...';
        calculateBtn.disabled = true;

        // Make AJAX request
        const formData = new FormData();
        formData.append('input', input);

        fetch('calculator.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            calculateBtn.textContent = 'Calculate';
            calculateBtn.disabled = false;

            if (data.error) {
                showError(data.error);
                return;
            }

            hideError();
            // Use setTimeout to ensure DOM is ready
            setTimeout(() => {
                displayResults(data);
            }, 10);
        })
        .catch(error => {
            calculateBtn.textContent = 'Calculate';
            calculateBtn.disabled = false;
            showError('An error occurred: ' + error.message);
        });
    });

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.remove('hidden');
        ipv4Results.classList.add('hidden');
        ipv6Results.classList.add('hidden');
        resultsDiv.classList.remove('hidden');
    }

    function hideError() {
        errorMessage.classList.add('hidden');
    }

    let currentIPv4Data = null;
    let currentIPv6Data = null;

    function displayResults(data) {
        resultsDiv.classList.remove('hidden');

        if (data.type === 'ipv4') {
            ipv4Results.classList.remove('hidden');
            ipv6Results.classList.add('hidden');
            currentIPv4Data = data;
            currentIPv6Data = null;

            // Populate IPv4 results
            document.getElementById('rir').textContent = data.rir || 'N/A';
            document.getElementById('host-dot').textContent = data.host || '';
            document.getElementById('decimal-repr').textContent = data.decimal || '';
            document.getElementById('hex-repr').textContent = data.hex || '';
            document.getElementById('subnet-cidr').textContent = data.subnet || '';
            document.getElementById('network-range').textContent = data.networkRange || '';
            document.getElementById('usable-range').textContent = data.usableRange || '';
            document.getElementById('broadcast').textContent = data.broadcast || '';
            document.getElementById('subnet-mask').textContent = data.subnetMask || '';
            document.getElementById('ptr-record').textContent = data.ptr || '';

            // Populate IPv4 prefix selector
            const prefixSelectIPv4 = document.getElementById('subnet-prefix-ipv4');
            if (prefixSelectIPv4) {
                prefixSelectIPv4.innerHTML = '<option value="">Select prefix</option>';
                // Get CIDR from data.cidr or extract from subnet
                let currentCidr = parseInt(data.cidr);
                if (isNaN(currentCidr) && data.subnet) {
                    const parts = data.subnet.split('/');
                    if (parts.length === 2) {
                        currentCidr = parseInt(parts[1]);
                    }
                }
                if (!isNaN(currentCidr) && currentCidr > 0 && currentCidr < 32) {
                    for (let i = currentCidr + 1; i <= 32; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = '/' + i;
                        prefixSelectIPv4.appendChild(option);
                    }
                }
            }

            // Clear subnetting results
            document.getElementById('ipv4-subnets-list').innerHTML = '';

        } else if (data.type === 'ipv6') {
            ipv6Results.classList.remove('hidden');
            ipv4Results.classList.add('hidden');
            currentIPv6Data = data;
            currentIPv4Data = null;

            // Populate IPv6 results
            document.getElementById('ipv6-subnet-cidr').textContent = data.subnet || '';
            document.getElementById('ipv6-network-range').textContent = data.networkRange || '';
            document.getElementById('ipv6-prefix').textContent = data.prefix || '';
            document.getElementById('ipv6-expanded').textContent = data.expanded || '';
            document.getElementById('ipv6-compressed').textContent = data.compressed || '';
            document.getElementById('ipv6-decimal').textContent = data.decimal || '';
            document.getElementById('ipv6-ptr-record').textContent = data.ptr || '';

            // Populate IPv6 prefix selector
            const prefixSelect = document.getElementById('subnet-prefix-ipv6');
            if (prefixSelect) {
                prefixSelect.innerHTML = '<option value="">Select prefix</option>';
                // Get CIDR from data.cidr or extract from subnet
                let currentCidr = parseInt(data.cidr);
                if (isNaN(currentCidr) && data.subnet) {
                    const parts = data.subnet.split('/');
                    if (parts.length === 2) {
                        currentCidr = parseInt(parts[1]);
                    }
                }
                if (!isNaN(currentCidr) && currentCidr > 0 && currentCidr < 128) {
                    for (let i = currentCidr + 1; i <= 128; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = '/' + i;
                        prefixSelect.appendChild(option);
                    }
                }
            }

            // Clear subnetting results
            document.getElementById('ipv6-subnets-list').innerHTML = '';
        }
    }

    // IPv4 Subnetting
    const subnetIPv4Btn = document.getElementById('subnet-ipv4-btn');
    subnetIPv4Btn.addEventListener('click', function() {
        if (!currentIPv4Data) return;

        const newCidr = parseInt(document.getElementById('subnet-prefix-ipv4').value);
        if (!newCidr) {
            alert('Please select a new prefix length');
            return;
        }

        const networkIp = currentIPv4Data.network || currentIPv4Data.subnet.split('/')[0];
        const originalCidr = currentIPv4Data.cidr;

        subnetIPv4Btn.textContent = 'Calculating...';
        subnetIPv4Btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'subnet_ipv4');
        formData.append('network', networkIp);
        formData.append('cidr', originalCidr);
        formData.append('new_cidr', newCidr);

        fetch('calculator.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            subnetIPv4Btn.textContent = 'Calculate Subnets';
            subnetIPv4Btn.disabled = false;

            if (data.error) {
                alert(data.error);
                return;
            }

            displayIPv4Subnets(data);
        })
        .catch(error => {
            subnetIPv4Btn.textContent = 'Calculate Subnets';
            subnetIPv4Btn.disabled = false;
            alert('Error: ' + error.message);
        });
    });

    function displayIPv4Subnets(data) {
        const listDiv = document.getElementById('ipv4-subnets-list');
        listDiv.innerHTML = '';

        if (!data.subnets || data.subnets.length === 0) {
            listDiv.innerHTML = '<p>No subnets found.</p>';
            return;
        }

        const info = document.createElement('div');
        info.style.marginBottom = '15px';
        info.style.padding = '10px';
        info.style.background = '#f0f0f0';
        const subnetInfo = data.hasMore 
            ? `<strong>Total Subnets:</strong> ${data.subnetCount.toLocaleString()} (showing first ${data.displayCount})`
            : `<strong>Subnets:</strong> ${data.subnetCount}`;
        info.innerHTML = `<strong>Original:</strong> /${data.originalCidr} → <strong>New:</strong> /${data.newCidr} | ${subnetInfo}`;
        listDiv.appendChild(info);

        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.style.marginTop = '10px';

        // Header
        const header = document.createElement('tr');
        header.style.background = '#333';
        header.style.color = 'white';
        ['#', 'Subnet', 'Network', 'Netmask', 'Broadcast', 'Usable Range', 'Hosts'].forEach(text => {
            const th = document.createElement('th');
            th.textContent = text;
            th.style.padding = '10px';
            th.style.textAlign = 'left';
            th.style.border = '1px solid #ddd';
            header.appendChild(th);
        });
        table.appendChild(header);

        // Rows
        data.subnets.forEach((subnet, index) => {
            const row = document.createElement('tr');
            row.style.borderBottom = '1px solid #ddd';
            
            // Get netmask - use provided value or show prefix as fallback
            let netmask = subnet.netmask;
            if (!netmask && data.newCidr) {
                netmask = '/' + data.newCidr;
            }
            
            const rowData = [
                index + 1, 
                subnet.subnet || '', 
                subnet.network || '', 
                netmask || '', 
                subnet.broadcast || '', 
                subnet.usable || '', 
                subnet.hosts || ''
            ];
            
            rowData.forEach(text => {
                const td = document.createElement('td');
                td.textContent = text || '';
                td.style.padding = '8px';
                td.style.border = '1px solid #ddd';
                td.style.fontFamily = 'monospace';
                if (index % 2 === 0) {
                    td.style.background = '#f9f9f9';
                }
                row.appendChild(td);
            });
            table.appendChild(row);
        });

        listDiv.appendChild(table);
    }

    // IPv6 Subnetting
    const subnetIPv6Btn = document.getElementById('subnet-ipv6-btn');
    subnetIPv6Btn.addEventListener('click', function() {
        if (!currentIPv6Data) return;

        const newCidr = parseInt(document.getElementById('subnet-prefix-ipv6').value);
        if (!newCidr) {
            alert('Please select a new prefix length');
            return;
        }

        const networkIp = currentIPv6Data.prefix;
        const originalCidr = currentIPv6Data.cidr;

        subnetIPv6Btn.textContent = 'Calculating...';
        subnetIPv6Btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'subnet_ipv6');
        formData.append('network', networkIp);
        formData.append('cidr', originalCidr);
        formData.append('new_cidr', newCidr);

        fetch('calculator.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            subnetIPv6Btn.textContent = 'Calculate Subnets';
            subnetIPv6Btn.disabled = false;

            if (data.error) {
                alert(data.error);
                return;
            }

            displayIPv6Subnets(data);
        })
        .catch(error => {
            subnetIPv6Btn.textContent = 'Calculate Subnets';
            subnetIPv6Btn.disabled = false;
            alert('Error: ' + error.message);
        });
    });

    function displayIPv6Subnets(data) {
        const listDiv = document.getElementById('ipv6-subnets-list');
        listDiv.innerHTML = '';

        const info = document.createElement('div');
        info.style.marginBottom = '15px';
        info.style.padding = '10px';
        info.style.background = '#f0f0f0';
        const subnetInfo = data.hasMore 
            ? `<strong>Total Subnets:</strong> ${data.subnetCount.toLocaleString()} (showing first ${data.displayCount})`
            : `<strong>Subnets:</strong> ${data.subnetCount}`;
        info.innerHTML = `<strong>Original:</strong> /${data.originalCidr} → <strong>New:</strong> /${data.newCidr} | ${subnetInfo}`;
        listDiv.appendChild(info);

        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.style.marginTop = '10px';

        // Header
        const header = document.createElement('tr');
        header.style.background = '#333';
        header.style.color = 'white';
        ['#', 'Subnet', 'Network', 'Prefix', 'Range'].forEach(text => {
            const th = document.createElement('th');
            th.textContent = text;
            th.style.padding = '10px';
            th.style.textAlign = 'left';
            th.style.border = '1px solid #ddd';
            header.appendChild(th);
        });
        table.appendChild(header);

        // Rows
        data.subnets.forEach((subnet, index) => {
            const row = document.createElement('tr');
            row.style.borderBottom = '1px solid #ddd';
            
            const prefix = '/' + data.newCidr;
            [index + 1, subnet.subnet, subnet.network, prefix, subnet.range].forEach(text => {
                const td = document.createElement('td');
                td.textContent = text;
                td.style.padding = '8px';
                td.style.border = '1px solid #ddd';
                td.style.fontFamily = 'monospace';
                td.style.fontSize = '0.9em';
                if (index % 2 === 0) {
                    td.style.background = '#f9f9f9';
                }
                row.appendChild(td);
            });
            table.appendChild(row);
        });

        listDiv.appendChild(table);
    }

    // Auto-detect IP type and adjust CIDR range
    ipInput.addEventListener('input', function() {
        const value = this.value;
        if (value.includes(':')) {
            // IPv6 - update CIDR select
            cidrSelect.innerHTML = '<option value="">Select CIDR</option>';
            for (let i = 0; i <= 128; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = '/' + i;
                cidrSelect.appendChild(option);
            }
        } else if (value.includes('.')) {
            // IPv4 - update CIDR select
            cidrSelect.innerHTML = '<option value="">Select CIDR</option>';
            for (let i = 0; i <= 32; i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = '/' + i;
                cidrSelect.appendChild(option);
            }
        }
    });
});
