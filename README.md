# CIDR Subnet Calculator

A powerful, modern, and easy-to-use PHP-based subnet calculator for IPv4 and IPv6 addresses. Calculate subnet information, perform subnetting operations, and get detailed network analysis - all in a clean, responsive web interface.

![PHP Version](https://img.shields.io/badge/PHP-7.0%2B-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## ✨ Features

### IPv4 & IPv6 Support
- **IPv4 Calculations**: Full subnet information for IPv4 addresses
- **IPv6 Calculations**: Complete prefix and subnet analysis for IPv6 addresses
- **Dual Protocol Support**: Seamlessly switch between IPv4 and IPv6

### Comprehensive Subnet Information
- **Network Range**: Start and end addresses of the network
- **Usable IP Range**: Host addresses available for use
- **Subnet Mask / Prefix**: Network mask in decimal (IPv4) or prefix length (IPv6)
- **Broadcast Address**: Broadcast address for IPv4 networks
- **Decimal & Hexadecimal**: IP address representations
- **PTR Records**: Reverse DNS record examples
- **RIR Information**: Regional Internet Registry detection (IPv4)

### Advanced Subnetting
- **IPv4 Subnetting**: Divide networks into smaller subnets by selecting new prefix length
  - View all subnet details including network, netmask, broadcast, and usable ranges
  - Display up to 512 subnets (shows first 512 if more exist)
- **IPv6 Subnetting**: Split IPv6 prefixes into smaller subnets
  - View subnet network addresses and ranges
  - Display up to 1024 subnets (shows first 1024 if more exist)

### User Interface
- **Clean & Simple Design**: Minimalist interface focused on functionality
- **Responsive Layout**: Works perfectly on desktop, tablet, and mobile devices
- **Interactive CIDR Selector**: Easy dropdown selection for CIDR notation
- **Real-time Calculations**: Fast AJAX-based calculations without page reload
- **Organized Results**: Well-structured tables and information display

## 🚀 Quick Start

### Requirements
- PHP 7.0 or higher
- Web server (Apache, Nginx, or PHP built-in server)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/MrAriaNet/cidr.git
   cd cidr
   ```

2. **Using PHP Built-in Server** (Recommended for testing)
   ```bash
   php -S localhost:8000
   ```
   Then open your browser and navigate to `http://localhost:8000`

3. **Using Apache/Nginx**
   - Place the files in your web root directory
   - Access via your domain or localhost
   - No additional configuration required

## 📖 Usage Guide

### Basic Calculation

1. **Enter IP Address**: Type an IP address with CIDR notation
   - IPv4 example: `192.168.1.0/24`
   - IPv6 example: `2001:db8::/64`

2. **Select CIDR** (Optional): Use the dropdown to select or modify the CIDR notation

3. **Calculate**: Click the "Calculate" button to see detailed subnet information

### Subnetting (Dividing Networks)

#### For IPv4:
1. Calculate the main subnet first (e.g., `192.168.1.0/24`)
2. In the "Subnetting" section, select a new prefix length (e.g., `/26` to create 4 subnets)
3. Click "Calculate Subnets"
4. View all subnets with their network addresses, netmasks, broadcast addresses, and usable ranges

#### For IPv6:
1. Calculate the main prefix first (e.g., `2001:db8::/48`)
2. In the "Subnetting" section, select a new prefix length (e.g., `/64`)
3. Click "Calculate Subnets"
4. View all subnets with their network addresses and ranges

## 💡 Examples

### IPv4 Examples
```
192.168.1.0/24     → Class C network (256 addresses)
10.0.0.0/8         → Class A network (16,777,216 addresses)
172.16.0.0/16      → Class B network (65,536 addresses)
192.168.1.0/26     → Small subnet (64 addresses, 62 usable)
```

### IPv6 Examples
```
2001:db8::/64      → Standard /64 subnet
2001:db8::/48      → Larger prefix
2001:0db8:85a3::8a2e:0370:7334/48
```

### Subnetting Examples
- **IPv4**: `192.168.1.0/24` → `/26` creates 4 subnets:
  - `192.168.1.0/26` (64 addresses)
  - `192.168.1.64/26` (64 addresses)
  - `192.168.1.128/26` (64 addresses)
  - `192.168.1.192/26` (64 addresses)

- **IPv6**: `2001:db8::/48` → `/64` creates 65,536 subnets

## 📁 Project Structure

```
cidr/
├── index.php          # Main HTML interface
├── calculator.php     # Backend calculation logic (PHP class)
├── style.css          # Stylesheet for the UI
├── script.js          # Frontend JavaScript for interactivity
└── README.md          # This file
```

### File Descriptions

- **index.php**: Contains the HTML structure, form inputs, and result display areas
- **calculator.php**: PHP class with methods for:
  - IPv4/IPv6 calculation
  - Subnetting operations
  - Network mask calculations
  - Address conversions
- **style.css**: Clean, minimal styling with responsive design
- **script.js**: Handles user interactions, AJAX requests, and dynamic content updates

## 🔧 Technical Details

### Technologies Used
- **PHP**: Server-side calculations and logic
- **JavaScript (Vanilla)**: Client-side interactivity
- **HTML5/CSS3**: Modern web standards
- **AJAX**: Asynchronous data fetching

### Key Functions

#### IPv4 Calculations
- Network address calculation
- Broadcast address determination
- Subnet mask generation
- Usable IP range calculation
- Host count calculation

#### IPv6 Calculations
- IPv6 address expansion and compression
- Prefix length calculations
- Network range determination
- Binary conversions for 128-bit addresses

#### Subnetting
- Automatic subnet generation based on prefix length
- Network boundary validation
- Efficient binary operations for large address spaces

## 🎯 Use Cases

- **Network Administrators**: Quick subnet planning and verification
- **System Engineers**: Network design and IP allocation
- **Students**: Learning CIDR notation and subnetting
- **Developers**: Network configuration and troubleshooting
- **IT Professionals**: Daily network management tasks

## 🌟 Key Advantages

- ✅ **No Dependencies**: Pure PHP, no frameworks or external libraries required
- ✅ **Fast Performance**: Efficient calculations with minimal overhead
- ✅ **Lightweight**: Small codebase, easy to understand and modify
- ✅ **Cross-Platform**: Works on any system with PHP
- ✅ **Mobile-Friendly**: Responsive design works on all devices
- ✅ **Open Source**: Free to use, modify, and distribute

## 🤝 Contributing

Contributions are welcome! If you have suggestions, bug reports, or want to add features:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👤 Author

**MrAriaNet**

- GitHub: [@MrAriaNet](https://github.com/MrAriaNet)

## 🙏 Acknowledgments

- Inspired by [cidr.eu](https://www.cidr.eu/en/calculator)
- Built with modern web standards
- Designed for simplicity and functionality

## 📧 Support

If you encounter any issues or have questions:
- Open an issue on GitHub
- Check existing issues for solutions
- Review the code comments for implementation details

---

**Made with ❤️ for the networking community**
