# Flarum Like Search

A Flarum extension that improves the default search functionality by implementing LIKE-based search, allowing for partial word matches.

## Features

- Partial word matching in discussion titles and content
- Maintains proper sorting by creation date
- Fully integrated with Flarum's search system
- No configuration needed

## Installation

Install with composer:

```bash
composer require fakethinkpad85/flarum-fulltextsearch
```

## Usage

Simply use the search box as normal. The extension will automatically enable partial word matching.

For example:
- Searching for "prog" will find discussions containing "programming"
- Searching for "tech" will find discussions containing "technology"

## License

This extension is licensed under the MIT License. See [License File](LICENSE) for more information.