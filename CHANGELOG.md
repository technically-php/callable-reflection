# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.3.0 - 2021-04-07
### Changed
- Make `CallableReflection` constructor private.
- Reflection for callables is now created with `CallableReflection::fromCallable()` 

### Added
- Add ability to reflect constructors: `CallableReflection::fromConstructor()`.

## 0.2.0 - 2021-04-07
### Added
- Add `ParameterReflection::satisfies()` method to check if value satisfies parameter type declaration.

### Changed
- Allow `null` to be used with `TypeReflection`.

## 0.1.0 - 2021-04-07
### Added
- Initial implementation.
