# -*- coding: utf-8 -*-

### General settings

extensions = []
templates_path = ['_templates']
source_suffix = '.rst'
master_doc = 'index'
project = u'Firebase PHP SDK'
author = u'Jérôme Gamez'
copyright = u'2015-2016, Jérôme Gamez'
version = u'1.x'
html_title = u'Firebase PHP SDK Documentation'
html_short_title = u'Firebase PHP SDK'

exclude_patterns = ['_build']
html_static_path = ['_static']

### Theme settings
import sphinx_rtd_theme

html_theme = 'sphinx_rtd_theme'
html_theme_path = [sphinx_rtd_theme.get_html_theme_path()]

### Syntax Highlighting
from sphinx.highlighting import lexers
from pygments.lexers.web import PhpLexer

lexers['php'] = PhpLexer(startinline=True, linenos=1)
lexers['php-annotations'] = PhpLexer(startinline=True, linenos=1)
primary_domain = u'php'

### Integrations

html_context = {
  "display_github": True,
  "github_user": "kreait",
  "github_repo": "firebase-php",
  "github_version": "1.x",
  "conf_py_path": "/docs/",
  "source_suffix": ".rst",
}
