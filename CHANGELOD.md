### v0.1.2
##### Features:
- Added option to declare class factories using `#[Factory]` attribute or from config;
- Added option to bind something after object creation using `#[Required]` attribute;
- Separated docs by functionality;
- Updated examples;
- Separated tests by configuration type, e.g. config or attribute;
- Basic refactoring.

### v0.1.1
##### Features:
- Added option to bind enum cases from config and `#[Parameter]` attribute.

### v0.1.0
##### Features:
- First release!

### v0.0.17
##### Features:
- Added option to inject `Temkaa\SimpleContainer\Container` and `Psr\Container\ContainerInterface` into class constructors.

### v0.0.16
##### Features:
- Set infection MSI to 100%;
- Added container compilation time performance;
- Added code coverage test.

### v0.0.15
##### Features:
- Set psalm level to 1.

### v0.0.14
##### Features:
- Refactoring.

### v0.0.13
##### Features:
- Added class auto discover for binding interfaces and decorators without explicit interface bounds.

### v0.0.12
##### Features:
- Added option for auto resolving decorated service if there is only one decorated service in constructor with different
name defined in attribute/config.

### v0.0.11
##### Features:
- Added option to add class aliases from config;
- Renamed examples so name is showing up the example purpose.
 
### v0.0.10
##### Features:
- Added composer keywords.

### v0.0.9
##### Features:
- Added docs and examples.

### v0.0.8
##### Features:
- Refactored services namespaces. 

### v0.0.7
##### Features:
- Dependencies cleanup.

### v0.0.6
##### Features:
- Changed config from yaml file to php object;
- Changed php version to `^8.3`;
- Added infection.

### v0.0.5
##### Features:
- Add decorator option.

### v0.0.4
##### Features:
- Added ability to declare non-singleton classes.

### v0.0.3
##### Features:
- Added global variable bindings from config.

### v0.0.2
##### Features:
- Full refactor.

### v0.0.1
##### Features:
- Base implementation.
