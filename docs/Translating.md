# Translating Hypixel.Paniek.de

Thank you for showing your interest in translating Hypixel.Paniek.de to your own language.

All possible translations can be found in the [/resources/lang](/resources/lang) folder of this project. For each supported language, there is a sub folder named after each corresponding language's 2 letter code.

### Editing translation strings

Translations are stored in the form of PHP arrays. They are not very special, so you don't need  to worry about having any programming knowledge whatsoever. What you do need to know, is that single `'` and double `"` quotation marks are used to store strings in PHP. If you want to use one of these characters in a string encapsulated by the same characters, you will need to escape the quotation mark with a backslash `\ `.

For example, the string `Hello! Max said: "I'm creating signatures"` would be written like this:

    'example_string' => 'Hello! Max said "I\'m creating signatures"

The double quotation marks are not escaped in this case because the string is encapsulated by single quotation marks.

### Adding a new language

Adding a new language is just as easy as updating an existing one. Create a new directory named after the [ISO-639-1 code](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) of the language.

If you want to add German for example, create a folder called `de` in `/resources/lang/`.

Now simply follow the instructions to update an existing one.

### Updating existing languages

All translation strings can be found in the corresponding PHP files named after the various sections of this website.

If you would like to update the English homepage for example, that would be done in the file `/resources/lang/en/home.php`.

The English translations are the base translations. If a translation is missing, the site will always try to fall back to the English translation files.

If you want to add translations for another language than English, you need to copy the file from the `en` folder to the folder of your language. You may then edit the translations in that file.

If you do not want to translate everything or are not able to translate everything, then please omit the values that are the same as English from the file. This ensures that the latest translations found in the `en` folder are used when a translation is missing.

After translating, push your work to GitHub and create a Pull Request to let us know of your work!

