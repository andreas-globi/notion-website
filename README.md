# Notion HTML Export Website Boilerplate

Make a website from your Notion notes with this simple boilerplate.

This boilerplate will quickly get your website up and running with the HTML export from Notion. 
It includes a simple header, footer, and ajax search function.

![](inc/screenshot.png)

This library used to try to fetch an export from Notion directly, but the internal API keeps changing
and the official API doesn't provide an export endpoint. So, the best way to get your content is to 
manually export your Notion notes to HTML and upload the zip file to the server.

## Requirements
- Web Server Running Apache with PHP > 7.2
- `php-zip` extension
- Notion HTML Export file

## Installation

1. Update the `config/vars.php` file with your settings
2. Clone the repository to your web folder
    ```
    git clone https://github.com/andreas-globi/notion-website.git .
    ```
3. Copy the Notion HTML Export Zip file into the `zips` folder
4. Run `extract.php` from the command line
    ```
    php extract.php
    ```
5. Visit the website in your browser
6. Enjoy!

## Update Content

1. Copy the new Notion HTML Export Zip file into the `zips` folder
2. Run `extract.php` from the command line
    ```
    php extract.php
    ```

## Features
- Simple Header and Footer
- Ajax Search Function
- Responsive Design
- Easy to Customize
- Fast Loading
- SEO Friendly
- Google Analytics

## Examples
- [Pushing Podio Blog](https://pushingpodio.globi.ca)
- [ProcFu Help](https://help.procfu.com/)
- [GlobiMail Help](https://help.globimail.com/)

## License

This project is licensed under the GNU AGPLv3 License - see the [LICENSE.txt](LICENSE.txt) file for details.
