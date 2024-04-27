<h1 align="center">safronik/helpers</h1>
<p align="center">
    <strong>A PHP library to ease coder life in a different ways</strong>
</p>

# About

Helps to operate with such thing as:
- Array
- Buffer
- Data
- Dir
- DNS
- HTTP
- IP
- Page
- Reflection
- String
- Surrounding
- Time
- Version

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
get([ip_types: array|string[] = [...]], [v4_only: bool = true]): array|mixed|null
getDecimal([ip: null|string = null]): int
isPrivateNetwork(ip: string, [ip_type: string = 'v4']): bool
isIPInMask(ip: string, cidr: array|string, [ip_type: string = 'v4'], [xtet_count: int = 0]): bool
convertLongMaskToNumber(long_mask: int): int
validate(ip: string): bool|string
cidrValidate(cidr: string): bool
normalizeIPv6(ip: string): string
reduceIPv6(ip: string): string
resolveIP(ip): string
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

## [BufferHelper.php](src%2FBufferHelper.php)

```php
getCSVMap(&csv: string): array
parseCSV(&scv_string: string): array
parseNSV(nsv_string: string): string[]
convertCSVToArray(&csv: string, [map: array = [...]]): array
convertCSVLineToArray(&csv: string, [map: array = [...]], [stripslashes: bool = false]): array
cleanUpCSV(&buffer: array): array
popCSVLine(&csv: string): string
```

## [DirHelper.php](src%2FDirHelper.php)
```php
isExist(path): bool
isEmpty(path): bool
create(path): void
```
## [DNSHelper.php](src%2FDNSHelper.php)
```php
resolveHost(host: string): string
```
## [HTTPHelper.php](src%2FHTTPHelper.php)
```php
http__request(url: string, [data: array = [...]], [presets: array|null|string = null], [opts: array = [...]]): array|string[]|bool|int|string
http__request__get_content(url: string): array|string[]|bool|int|mixed|string
http__request__get_response_code(url: string): array|string[]|bool|int|mixed|string
http__get_data_from_remote_gz(url: string): array|false|mixed|string
get_data_from_local_gz(path: string): array|string[]|mixed|string
http__download_remote_file(url, tmp_folder): array|string[]|bool|int|string
http__download_remote_file__multi(urls: array, [write_to: string = '']): array|string[]
```
## [PageHelper.php](src%2FPageHelper.php)
```php
hasError(string_page): bool
```
## [StringHelper.php](src%2FStringHelper.php)
```php
removeNonUTF8(data: array|object|string): array|object|string
toUTF8(data: array|object|string, [data_codepage: null|string = null]): array|object|string
fromUTF8(obj: array|object|string, [data_codepage: null|string = null]): mixed
```
## [SurroundingHelper.php](src%2FSurroundingHelper.php)
```php
isWindows(): bool
isExtensionLoaded(extension_name): bool
```
## [TimeHelper.php](src%2FTimeHelper.php)
```php
getIntervalStart(interval: int): int
```
## [VersionHelper.php](src%2FVersionHelper.php)
```php
isCorrectSemanticVersion(version: string): bool
standardizeVersion(version): string
compare(version1: string, version2: string): int
```