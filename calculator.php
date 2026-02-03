<?php
// Disable error display to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

header('Content-Type: application/json');

class CIDRCalculator {
    
    public function calculate($input) {
        $input = trim($input);
        
        if (empty($input)) {
            return ['error' => 'Please enter an IP address with CIDR notation'];
        }
        
        // Check if IPv6
        if (strpos($input, ':') !== false) {
            return $this->calculateIPv6($input);
        } else {
            return $this->calculateIPv4($input);
        }
    }
    
    private function calculateIPv4($input) {
        // Parse input: IP/CIDR
        if (strpos($input, '/') === false) {
            return ['error' => 'Invalid format. Please use IP/CIDR notation (e.g., 192.168.1.0/24)'];
        }
        
        list($ip, $cidr) = explode('/', $input);
        $cidr = (int)$cidr;
        
        if ($cidr < 0 || $cidr > 32) {
            return ['error' => 'CIDR must be between 0 and 32 for IPv4'];
        }
        
        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return ['error' => 'Invalid IPv4 address'];
        }
        
        // Calculate subnet mask
        $mask = (0xFFFFFFFF << (32 - $cidr)) & 0xFFFFFFFF;
        $subnetMask = long2ip($mask);
        
        // Network address
        $network = $ipLong & $mask;
        $networkIp = long2ip($network);
        
        // Broadcast address
        $broadcast = $network | (~$mask & 0xFFFFFFFF);
        $broadcastIp = long2ip($broadcast);
        
        // Number of addresses
        $totalAddresses = pow(2, 32 - $cidr);
        $usableAddresses = max(0, $totalAddresses - 2);
        
        // First and last usable
        $firstUsable = $network + 1;
        $lastUsable = $broadcast - 1;
        
        if ($cidr == 32) {
            $firstUsable = $network;
            $lastUsable = $network;
            $usableAddresses = 1;
        } elseif ($cidr == 31) {
            $firstUsable = $network;
            $lastUsable = $broadcast;
            $usableAddresses = 2;
        }
        
        $firstUsableIp = long2ip($firstUsable);
        $lastUsableIp = long2ip($lastUsable);
        
        // Decimal representation
        $decimal = sprintf('%u', $ipLong);
        
        // Hex representation
        $hex = '0x' . str_pad(dechex($ipLong & 0xFFFFFFFF), 8, '0', STR_PAD_LEFT);
        
        // PTR record
        $ptrParts = array_reverse(explode('.', $ip));
        $ptrRecord = implode('.', $ptrParts) . '.in-addr.arpa';
        
        // RIR (simplified - would need actual RIR database for accuracy)
        $rir = $this->getRIR($ip);
        
        return [
            'type' => 'ipv4',
            'host' => $ip,
            'cidr' => $cidr,
            'network' => $networkIp,
            'subnet' => $networkIp . '/' . $cidr,
            'networkRange' => $networkIp . ' - ' . $broadcastIp . ' (' . number_format($totalAddresses) . ' unique addresses)',
            'usableRange' => $firstUsableIp . ' - ' . $lastUsableIp . ' (' . number_format($usableAddresses) . ' usable)',
            'broadcast' => $broadcastIp,
            'subnetMask' => $subnetMask,
            'decimal' => $decimal,
            'hex' => $hex,
            'ptr' => $ptrRecord,
            'rir' => $rir
        ];
    }
    
    private function calculateIPv6($input) {
        // Parse input: IP/CIDR
        if (strpos($input, '/') === false) {
            return ['error' => 'Invalid format. Please use IP/CIDR notation (e.g., 2001:db8::/64)'];
        }
        
        list($ip, $cidr) = explode('/', $input);
        $cidr = (int)$cidr;
        
        if ($cidr < 0 || $cidr > 128) {
            return ['error' => 'CIDR must be between 0 and 128 for IPv6'];
        }
        
        // Validate IPv6
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return ['error' => 'Invalid IPv6 address'];
        }
        
        // Expand IPv6 address
        $expanded = $this->expandIPv6($ip);
        
        // Compressed (normalized)
        $compressed = $this->compressIPv6($ip);
        
        // Calculate network prefix
        $networkPrefix = $this->getIPv6Network($ip, $cidr);
        
        // Calculate range
        $startRange = $this->getIPv6RangeStart($ip, $cidr);
        $endRange = $this->getIPv6RangeEnd($ip, $cidr);
        
        // Decimal representation (simplified - full 128-bit would be very long)
        $decimal = $this->ipv6ToDecimal($ip);
        
        // PTR record
        $ptrRecord = $this->getIPv6PTR($ip);
        
        // Number of /64 subnets (if applicable)
        $subnet64Count = '';
        if ($cidr <= 64) {
            $subnet64Count = pow(2, 64 - $cidr);
            if ($subnet64Count > 1) {
                $subnet64Count = number_format($subnet64Count) . ' /64 subnets';
            } else {
                $subnet64Count = '';
            }
        }
        
        return [
            'type' => 'ipv6',
            'cidr' => $cidr,
            'subnet' => $networkPrefix . '/' . $cidr,
            'networkRange' => $startRange . ' - ' . $endRange . ($subnet64Count ? ' = ' . $subnet64Count : ''),
            'prefix' => $networkPrefix,
            'expanded' => $expanded,
            'compressed' => $compressed,
            'decimal' => $decimal,
            'ptr' => $ptrRecord
        ];
    }
    
    private function expandIPv6($ip) {
        // Expand compressed IPv6
        if (strpos($ip, '::') !== false) {
            $parts = explode('::', $ip);
            $left = explode(':', $parts[0]);
            $right = isset($parts[1]) ? explode(':', $parts[1]) : [];
            
            $missing = 8 - count($left) - count($right);
            $zeros = array_fill(0, $missing, '0000');
            
            $allParts = array_merge($left, $zeros, $right);
        } else {
            $allParts = explode(':', $ip);
        }
        
        // Pad each part to 4 hex digits
        $expanded = [];
        foreach ($allParts as $part) {
            $expanded[] = str_pad($part, 4, '0', STR_PAD_LEFT);
        }
        
        return implode(':', $expanded);
    }
    
    private function compressIPv6($ip) {
        // Normalize and compress IPv6
        $expanded = $this->expandIPv6($ip);
        $parts = explode(':', $expanded);
        
        // Find longest sequence of zeros
        $maxZeroStart = -1;
        $maxZeroLen = 0;
        $currentZeroStart = -1;
        $currentZeroLen = 0;
        
        for ($i = 0; $i < count($parts); $i++) {
            if ($parts[$i] === '0000') {
                if ($currentZeroStart === -1) {
                    $currentZeroStart = $i;
                    $currentZeroLen = 1;
                } else {
                    $currentZeroLen++;
                }
            } else {
                if ($currentZeroLen > $maxZeroLen) {
                    $maxZeroLen = $currentZeroLen;
                    $maxZeroStart = $currentZeroStart;
                }
                $currentZeroStart = -1;
                $currentZeroLen = 0;
            }
        }
        
        if ($currentZeroLen > $maxZeroLen) {
            $maxZeroLen = $currentZeroLen;
            $maxZeroStart = $currentZeroStart;
        }
        
        // Compress
        if ($maxZeroLen > 1) {
            $before = array_slice($parts, 0, $maxZeroStart);
            $after = array_slice($parts, $maxZeroStart + $maxZeroLen);
            
            $compressed = [];
            if (!empty($before)) {
                $compressed = array_merge($compressed, $before);
            }
            $compressed[] = '';
            if (!empty($after)) {
                $compressed = array_merge($compressed, $after);
            }
            
            $result = implode(':', $compressed);
            if ($result[0] === ':') {
                $result = ':' . $result;
            }
            if (substr($result, -1) === ':') {
                $result = $result . ':';
            }
            return $result;
        }
        
        // Remove leading zeros from each part
        $result = [];
        foreach ($parts as $part) {
            $result[] = ltrim($part, '0') ?: '0';
        }
        
        return implode(':', $result);
    }
    
    private function getIPv6Network($ip, $cidr) {
        $expanded = $this->expandIPv6($ip);
        $parts = explode(':', $expanded);
        
        // Convert to binary
        $binary = '';
        foreach ($parts as $part) {
            $binary .= str_pad(decbin(hexdec($part)), 16, '0', STR_PAD_LEFT);
        }
        
        // Apply mask
        $masked = substr($binary, 0, $cidr) . str_repeat('0', 128 - $cidr);
        
        // Convert back to hex
        $networkParts = [];
        for ($i = 0; $i < 128; $i += 16) {
            $networkParts[] = str_pad(dechex(bindec(substr($masked, $i, 16))), 4, '0', STR_PAD_LEFT);
        }
        
        $network = implode(':', $networkParts);
        return $this->compressIPv6($network);
    }
    
    private function getIPv6RangeStart($ip, $cidr) {
        return $this->getIPv6Network($ip, $cidr);
    }
    
    private function getIPv6RangeEnd($ip, $cidr) {
        $expanded = $this->expandIPv6($ip);
        $parts = explode(':', $expanded);
        
        // Convert to binary
        $binary = '';
        foreach ($parts as $part) {
            $binary .= str_pad(decbin(hexdec($part)), 16, '0', STR_PAD_LEFT);
        }
        
        // Apply mask and set host bits to 1
        $masked = substr($binary, 0, $cidr) . str_repeat('1', 128 - $cidr);
        
        // Convert back to hex
        $networkParts = [];
        for ($i = 0; $i < 128; $i += 16) {
            $networkParts[] = str_pad(dechex(bindec(substr($masked, $i, 16))), 4, '0', STR_PAD_LEFT);
        }
        
        $end = implode(':', $networkParts);
        return $this->compressIPv6($end);
    }
    
    private function ipv6ToDecimal($ip) {
        // Convert IPv6 to decimal (simplified representation)
        $expanded = $this->expandIPv6($ip);
        $parts = explode(':', $expanded);
        
        $decimal = '';
        foreach ($parts as $part) {
            $decimal .= str_pad(decbin(hexdec($part)), 16, '0', STR_PAD_LEFT);
        }
        
        // Convert binary to decimal (for very large numbers, we'll use a simplified approach)
        return 'Unsigned 128-bit integer (too large to display)';
    }
    
    private function getIPv6PTR($ip) {
        $expanded = $this->expandIPv6($ip);
        $parts = explode(':', $expanded);
        
        // Reverse and expand each hex digit
        $reversed = [];
        foreach (array_reverse($parts) as $part) {
            $digits = str_split($part);
            foreach ($digits as $digit) {
                $reversed[] = $digit;
            }
        }
        
        return implode('.', $reversed) . '.ip6.arpa';
    }
    
    private function getRIR($ip) {
        // Simplified RIR detection (would need actual RIR database for accuracy)
        $ipLong = ip2long($ip);
        
        // APNIC: 1.0.0.0 - 1.255.255.255, 14.0.0.0 - 14.255.255.255, etc.
        // RIPE: 2.0.0.0 - 2.255.255.255, 5.0.0.0 - 5.255.255.255, etc.
        // ARIN: 3.0.0.0 - 3.255.255.255, 4.0.0.0 - 4.255.255.255, etc.
        // LACNIC: 177.0.0.0 - 177.255.255.255, etc.
        // AFRINIC: 41.0.0.0 - 41.255.255.255, etc.
        
        $firstOctet = (int)($ipLong >> 24) & 0xFF;
        
        if ($firstOctet >= 1 && $firstOctet <= 1) return 'APNIC';
        if ($firstOctet >= 2 && $firstOctet <= 2) return 'RIPE NCC';
        if ($firstOctet >= 3 && $firstOctet <= 4) return 'ARIN';
        if ($firstOctet >= 41 && $firstOctet <= 41) return 'AFRINIC';
        if ($firstOctet >= 177 && $firstOctet <= 177) return 'LACNIC';
        
        return 'Unknown';
    }
    
    public function subnetIPv4($networkIp, $originalCidr, $newCidr) {
        if ($newCidr <= $originalCidr || $newCidr > 32) {
            return ['error' => 'New prefix length must be greater than original and <= 32'];
        }
        
        $networkLong = ip2long($networkIp);
        if ($networkLong === false) {
            return ['error' => 'Invalid network address'];
        }
        
        // Calculate subnet size
        $subnetSize = pow(2, 32 - $newCidr);
        
        // Calculate number of subnets
        $subnetBits = $newCidr - $originalCidr;
        $subnetCount = pow(2, $subnetBits);
        
        // Limit to reasonable number
        if ($subnetCount > 256) {
            return ['error' => 'Too many subnets. Maximum 256 subnets allowed.'];
        }
        
        // Calculate new subnet mask
        $newMask = (0xFFFFFFFF << (32 - $newCidr)) & 0xFFFFFFFF;
        $newSubnetMask = long2ip($newMask);
        
        // Original network boundaries
        $originalMask = (0xFFFFFFFF << (32 - $originalCidr)) & 0xFFFFFFFF;
        $originalNetwork = $networkLong & $originalMask;
        $originalBroadcast = $originalNetwork | (~$originalMask & 0xFFFFFFFF);
        
        $subnets = [];
        
        for ($i = 0; $i < $subnetCount; $i++) {
            $subnetStart = $originalNetwork + ($i * $subnetSize);
            $subnetEnd = $subnetStart + $subnetSize - 1;
            
            // Check if subnet exceeds original network
            if ($subnetEnd > $originalBroadcast) {
                break; // Stop if we exceed original network
            }
            
            $subnetStartIp = long2ip($subnetStart);
            $subnetEndIp = long2ip($subnetEnd);
            $broadcastIp = long2ip($subnetEnd);
            
            $usableStart = $subnetStart + 1;
            $usableEnd = $subnetEnd - 1;
            if ($newCidr == 32) {
                $usableStart = $subnetStart;
                $usableEnd = $subnetStart;
            } elseif ($newCidr == 31) {
                $usableStart = $subnetStart;
                $usableEnd = $subnetEnd;
            }
            
            $subnets[] = [
                'subnet' => $subnetStartIp . '/' . $newCidr,
                'network' => $subnetStartIp,
                'broadcast' => $broadcastIp,
                'netmask' => $newSubnetMask,
                'range' => $subnetStartIp . ' - ' . $broadcastIp,
                'usable' => long2ip($usableStart) . ' - ' . long2ip($usableEnd),
                'hosts' => max(1, $subnetSize - ($newCidr >= 31 ? 0 : 2))
            ];
        }
        
        return [
            'originalCidr' => $originalCidr,
            'newCidr' => $newCidr,
            'subnetCount' => $subnetCount,
            'displayCount' => count($subnets),
            'hasMore' => $hasMore,
            'subnets' => $subnets
        ];
    }
    
    public function subnetIPv6($networkIp, $originalCidr, $newCidr) {
        if ($newCidr <= $originalCidr || $newCidr > 128) {
            return ['error' => 'New prefix length must be greater than original and <= 128'];
        }
        
        // Validate IPv6
        if (!filter_var($networkIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return ['error' => 'Invalid IPv6 address'];
        }
        
        // Expand network address
        $expanded = $this->expandIPv6($networkIp);
        $parts = explode(':', $expanded);
        
        // Convert to binary
        $binary = '';
        foreach ($parts as $part) {
            $binary .= str_pad(decbin(hexdec($part)), 16, '0', STR_PAD_LEFT);
        }
        
        // Get network part
        $networkBinary = substr($binary, 0, $originalCidr);
        
        // Calculate number of subnets
        $subnetBits = $newCidr - $originalCidr;
        $subnetCount = pow(2, $subnetBits);
        
        // Limit display to reasonable number (but allow calculation)
        $maxDisplay = 1024; // Maximum subnets to display
        $displayCount = min($subnetCount, $maxDisplay);
        $hasMore = $subnetCount > $maxDisplay;
        
        $subnets = [];
        
        for ($i = 0; $i < $displayCount; $i++) {
            // Create subnet binary
            $subnetBinary = $networkBinary . str_pad(decbin($i), $subnetBits, '0', STR_PAD_LEFT);
            $subnetBinary = str_pad($subnetBinary, 128, '0', STR_PAD_RIGHT);
            
            // Convert back to IPv6
            $subnetParts = [];
            for ($j = 0; $j < 128; $j += 16) {
                $subnetParts[] = str_pad(dechex(bindec(substr($subnetBinary, $j, 16))), 4, '0', STR_PAD_LEFT);
            }
            
            $subnetIp = implode(':', $subnetParts);
            $subnetIp = $this->compressIPv6($subnetIp);
            
            // Calculate range
            $rangeStart = $subnetIp;
            $rangeEndBinary = substr($subnetBinary, 0, $newCidr) . str_repeat('1', 128 - $newCidr);
            $rangeEndParts = [];
            for ($j = 0; $j < 128; $j += 16) {
                $rangeEndParts[] = str_pad(dechex(bindec(substr($rangeEndBinary, $j, 16))), 4, '0', STR_PAD_LEFT);
            }
            $rangeEnd = implode(':', $rangeEndParts);
            $rangeEnd = $this->compressIPv6($rangeEnd);
            
            $subnets[] = [
                'subnet' => $subnetIp . '/' . $newCidr,
                'network' => $subnetIp,
                'range' => $rangeStart . ' - ' . $rangeEnd
            ];
        }
        
        return [
            'originalCidr' => $originalCidr,
            'newCidr' => $newCidr,
            'subnetCount' => $subnetCount,
            'displayCount' => count($subnets),
            'hasMore' => $hasMore,
            'subnets' => $subnets
        ];
    }
}

// Handle request
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : 'calculate';
        $calculator = new CIDRCalculator();
        
        if ($action === 'subnet_ipv4') {
            $networkIp = isset($_POST['network']) ? $_POST['network'] : '';
            $originalCidr = isset($_POST['cidr']) ? (int)$_POST['cidr'] : 0;
            $newCidr = isset($_POST['new_cidr']) ? (int)$_POST['new_cidr'] : 0;
            $result = $calculator->subnetIPv4($networkIp, $originalCidr, $newCidr);
        } elseif ($action === 'subnet_ipv6') {
            $networkIp = isset($_POST['network']) ? $_POST['network'] : '';
            $originalCidr = isset($_POST['cidr']) ? (int)$_POST['cidr'] : 0;
            $newCidr = isset($_POST['new_cidr']) ? (int)$_POST['new_cidr'] : 0;
            $result = $calculator->subnetIPv6($networkIp, $originalCidr, $newCidr);
        } else {
            $input = isset($_POST['input']) ? $_POST['input'] : '';
            $result = $calculator->calculate($input);
        }
        
        // Clear any unexpected output
        ob_clean();
        echo json_encode($result);
    } else {
        ob_clean();
        echo json_encode(['error' => 'Invalid request method']);
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} catch (Error $e) {
    ob_clean();
    echo json_encode(['error' => 'Fatal error: ' . $e->getMessage()]);
}
?>
