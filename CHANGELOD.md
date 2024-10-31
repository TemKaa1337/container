### v0.2.5
##### Fixes:
- Fixed container compilation failing when property has bound value from env variable, but env variable does not exist
and property has default value.

### v0.2.4
##### Features:
- Added [ConfigProvider.php](src/Provider/Config/ProviderInterface.php), which is now can be added to config builder
instead of config itself.

### v0.2.3
##### Features:
- Allowed passing `exclude` classes to `InstanceOfIterator` and `TaggedIterator` to allow implementing interfaces
on some kind of `composite` classes.

### v0.2.2
##### Features:
- Changed namespaces, package name, etc from `Temkaa\SimpleContainer` to `Temkaa\Container`.

### v0.2.1
##### Fixes:
- Fixed bug when container cannot be compiled, when including folder and excluding file inside included folder.

### v0.2.0
##### Features:
- Beta release.

### v0.1.8
##### Features:
- Moved all scripts from Makefile to composer.

### v0.1.7
##### Features:
- Allow adding tags to interfaces and abstract classes using config. 

### v0.1.6
##### Features:
- Reworked `Decorates` attribute and config binding by deleting `signature` attribute. 

### v0.1.5
##### Features:
- Add container option to resolve classes with default value parameters when container itself cannot resolve dependency. 

### v0.1.4
##### Features:
- Renamed `Tagged` attribute to `TaggedIterator`;
- Added `InstanceOfIterator` attribute to allow binding array of object which are subclasses of given class.

### v0.1.3
##### Features:
- Reworked binding tagged iterator from config from `->bindValue('variable', '!tagged tag')` to just `new Tagged()`.

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
- Beta release.

### v0.0.17
##### Features:
- Added option to inject `Temkaa\Container\Container` and `Psr\Container\ContainerInterface` into class constructors.

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
