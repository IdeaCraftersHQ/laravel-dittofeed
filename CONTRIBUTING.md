# Contributing to Dittofeed Laravel SDK

Thank you for considering contributing to the Dittofeed Laravel SDK! We welcome contributions from the community.

## How to Contribute

### Reporting Bugs

If you find a bug, please open an issue on GitHub with:

1. A clear description of the bug
2. Steps to reproduce the issue
3. Expected behavior
4. Actual behavior
5. Laravel and PHP versions
6. Any relevant code samples or error messages

### Suggesting Features

We're always looking for ways to improve the SDK. To suggest a feature:

1. Open an issue on GitHub
2. Describe the feature and its use case
3. Explain why it would be valuable
4. Provide examples of how it would be used

### Pull Requests

We actively welcome pull requests:

1. Fork the repository
2. Create a new branch from `main`
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Update documentation as needed
7. Submit a pull request

#### Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/laravel-dittofeed.git
cd laravel-dittofeed

# Install dependencies
composer install

# Run tests
composer test

# Run code formatting
composer format

# Run static analysis
composer analyse
```

#### Code Style

We follow PSR-12 coding standards. Please ensure your code:

- Follows PSR-12
- Is well-documented with PHPDoc comments
- Includes type hints where possible
- Has meaningful variable and method names

Run Laravel Pint to format your code:

```bash
composer format
```

#### Testing

All new features must include tests:

- Write unit tests for individual components
- Write feature tests for integration scenarios
- Ensure test coverage remains above 90%

Run tests:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

#### Documentation

Update documentation for:

- New features
- API changes
- Configuration options
- Usage examples

Documentation includes:
- README.md
- Inline code comments
- PHPDoc blocks

### Code Review Process

1. All pull requests require review before merging
2. Reviewers will check for:
   - Code quality and style
   - Test coverage
   - Documentation
   - Backwards compatibility
3. Address reviewer feedback
4. Once approved, maintainers will merge

### Commit Messages

Write clear, descriptive commit messages:

```
Add support for custom event properties in model trait

- Added getDittofeedProperties method to trait
- Updated documentation with examples
- Added tests for custom properties
```

Format:
- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- First line is a summary (50 chars or less)
- Blank line after summary
- Detailed description if needed

### Branch Naming

Use descriptive branch names:

- `feature/custom-properties` - New features
- `fix/validation-error` - Bug fixes
- `docs/usage-examples` - Documentation updates
- `refactor/client-structure` - Refactoring

### Release Process

Releases are managed by maintainers:

1. Update CHANGELOG.md
2. Update version in composer.json
3. Create a git tag
4. Push to Packagist

## Community

- Be respectful and inclusive
- Help others learn and grow
- Focus on constructive feedback
- Celebrate contributions of all sizes

## Questions?

If you have questions about contributing, please:

- Open a discussion on GitHub
- Join our Discord community
- Check existing issues and pull requests

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

Thank you for contributing to Dittofeed Laravel SDK!
