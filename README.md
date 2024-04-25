<h1 align="center">safronik/helpers</h1>
<p align="center">
    <strong>A PHP library to ease coder life in a different ways</strong>
</p>

# About

Helps to operate with such thing as:

- Arrays
- Data
- IP
- Reflection

# Installation

The preferred method of installation is via Composer. Run the following
command to install the package and add it as a requirement to your project's
`composer.json`:

```bash
composer require safronik/helpers
```
or just download files or clone repository (in this case you should bother about autoloader)

# Usage

## [HelperArray](src%2FHelperArray.php)
```php
HelperArray::insert( $array_passed by_link, $insert_position, $insert_value); // Modifies the array $array. Paste $insert on $position
```
## [HelperData](src%2FHelperData.php)
```php
$array_from_json = HelperData::unpackIfJSON( $json_data );
$token           = HelperData::createToken(); // Generates UUID
```
## [HelperIP](src%2FHelperIP.php)

```php
$ip_string = HelperIP::get()        // Returns the current IPv4 address
$ip_long   = HelperIP::getDecimal() // Returns the current IPv4 address converted to long type
$ip_long   = HelperIP::getDecimal( '127.0.0.1' ) // Returns 127.0.0.1 IPv4 address converted to long type -> 2130706433
$is_valid  = HelperIP::validate( '127.0.0.1' )   // Validate if the given value is IPv4 address
```
## [HelperReflection](src%2FHelperReflection.php)
```php
// Scan directory and its subdirectories. Could filter the set by different parameters
HelperReflection::getClassesFromDirectory(
    directory: string,                      // Directory path
    namespace: string,                      // Directory namespace
    [exclusions: string[] = [...]],         // Exclusions. Full strict comparison 
    [filter: string = ''],                  // Positive filter (only string contains will be present in the result set)  
    [recursive: bool = false],              // Scan subdirectories 
    [skip_infrastructure: bool = true],     // Skip classes starts with '_' (underscore symbol)
    [filter_callback: callable|null = null] // Any callback you want to filter the result set. Other methods of self could be passed as a callback filter 
): array

// Filter everything except final classes from the given set 
HelperReflection::filterFinalClasses(classes): array

// Filter everything except interfaces from the given set 
HelperReflection::getInterfacesFromDirectory(classes): array

// Check if the given class use specific trait
HelperReflection::isClassUseTrait(classname: string, trait: string): bool

// Gets class traits
HelperReflection::getClassTraits(classname: string): array

// Filter everything except classes which are implement specific interface from the given set 
HelperReflection::filterClassesByInterface(classes: array, interface: string): array

// // Check if the given class implements specific interface
HelperReflection::isClassHasInterface(class: object|string, interface: string): bool
```