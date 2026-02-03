<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIDR Subnet Calculator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Subnet Calculator</h1>
            <p class="subtitle">Calculate IPv4 and IPv6 subnet information</p>
        </header>

        <div class="calculator-form">
            <div class="input-group">
                <label for="ip-address">IPv4 or IPv6 address</label>
                <div class="input-wrapper">
                    <input type="text" id="ip-address" placeholder="192.168.1.0/24 or 2001:db8::/64" autocomplete="off">
                    <select id="cidr-select" class="cidr-select">
                        <option value="">Select CIDR</option>
                    </select>
                </div>
            </div>

            <button id="calculate-btn" class="calculate-btn">Calculate</button>
        </div>

        <div id="results" class="results hidden">
            <div id="ipv4-results" class="result-section hidden">
                <h2>IPv4 Subnet Information</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <label>Regional Internet Registry</label>
                        <div class="value" id="rir"></div>
                    </div>
                    <div class="info-item">
                        <label>Host (Dot-decimal notation)</label>
                        <div class="value" id="host-dot"></div>
                    </div>
                    <div class="info-item">
                        <label>Decimal Representation</label>
                        <div class="value" id="decimal-repr"></div>
                    </div>
                    <div class="info-item">
                        <label>Hex Representation</label>
                        <div class="value" id="hex-repr"></div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <label>Subnet (CIDR notation)</label>
                        <div class="value" id="subnet-cidr"></div>
                    </div>
                    <div class="info-item">
                        <label>Network Range</label>
                        <div class="value" id="network-range"></div>
                    </div>
                    <div class="info-item">
                        <label>Usable Range</label>
                        <div class="value" id="usable-range"></div>
                    </div>
                    <div class="info-item">
                        <label>Broadcast Address</label>
                        <div class="value" id="broadcast"></div>
                    </div>
                    <div class="info-item">
                        <label>Subnet Mask</label>
                        <div class="value" id="subnet-mask"></div>
                    </div>
                </div>

                <div class="info-item">
                    <label>PTR Record Example (Reverse DNS)</label>
                    <div class="value" id="ptr-record"></div>
                </div>

                <div class="subnetting-section">
                    <h3>Subnetting</h3>
                    <div class="subnetting-controls">
                        <label for="subnet-prefix-ipv4">New prefix length:</label>
                        <select id="subnet-prefix-ipv4" style="padding: 8px; margin: 0 10px;">
                            <option value="">Select prefix</option>
                        </select>
                        <button id="subnet-ipv4-btn" class="subnet-btn">Calculate Subnets</button>
                    </div>
                    <div id="ipv4-subnets-list" class="subnets-list"></div>
                </div>
            </div>

            <div id="ipv6-results" class="result-section hidden">
                <h2>IPv6 Subnet Information</h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <label>Subnet Prefix (CIDR notation)</label>
                        <div class="value" id="ipv6-subnet-cidr"></div>
                    </div>
                    <div class="info-item">
                        <label>Network Range</label>
                        <div class="value" id="ipv6-network-range"></div>
                    </div>
                    <div class="info-item">
                        <label>Prefix Address</label>
                        <div class="value" id="ipv6-prefix"></div>
                    </div>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <label>Expanded Address</label>
                        <div class="value" id="ipv6-expanded"></div>
                    </div>
                    <div class="info-item">
                        <label>Compressed Address</label>
                        <div class="value" id="ipv6-compressed"></div>
                    </div>
                    <div class="info-item">
                        <label>Decimal Representation</label>
                        <div class="value" id="ipv6-decimal"></div>
                    </div>
                </div>

                <div class="info-item">
                    <label>PTR Record Example (Reverse DNS)</label>
                    <div class="value" id="ipv6-ptr-record"></div>
                </div>

                <div class="subnetting-section">
                    <h3>Subnetting</h3>
                    <div class="subnetting-controls">
                        <label for="subnet-prefix-ipv6">New prefix length:</label>
                        <select id="subnet-prefix-ipv6" style="padding: 8px; margin: 0 10px;">
                            <option value="">Select prefix</option>
                        </select>
                        <button id="subnet-ipv6-btn" class="subnet-btn">Calculate Subnets</button>
                    </div>
                    <div id="ipv6-subnets-list" class="subnets-list"></div>
                </div>
            </div>

            <div id="error-message" class="error-message hidden"></div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
