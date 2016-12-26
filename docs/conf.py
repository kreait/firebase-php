# -*- coding: utf-8 -*-

### General settings

extensions = []
templates_path = ['_templates']
source_suffix = '.rst'
master_doc = 'index'
project = u'Firebase Admin SDK for PHP'
author = u'Jérôme Gamez'
copyright = u'2016, Jérôme Gamez'
version = u'2.x'
html_title = u'Firebase Admin SDK for PHP Documentation'
html_short_title = u'Firebase Admin SDK for PHP'

exclude_patterns = ['_build']
html_static_path = ['_static']

suppress_warnings = ['image.nonlocal_uri']

### Theme settings
import sphinx_rtd_theme

html_theme = 'sphinx_rtd_theme'
html_theme_path = [sphinx_rtd_theme.get_html_theme_path()]

### Syntax Highlighting
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

lexers['php'] = PhpLexer(startinline=True, linenos=1)
lexers['php-annotations'] = PhpLexer(startinline=True, linenos=1)

### Integrations

html_context = {
  "display_github": True,
  "github_user": "kreait",
  "github_repo": "firebase-php",
  "github_version": "master",
  "conf_py_path": "/docs/",
  "source_suffix": ".rst",
}
