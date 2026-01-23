# LM-Commons Documentation Theme Files

This repository contains tooling and theme files to build sub-websites for
LM-Commons packages.

LM-Commons uses [Docusaurus](https://docusaurus.io) to build static documentation websites. It is
mandatory to understand how Docusaurus works to develop documentation for the
package.

## Installation Instructions

To add a documentation website for a package, clone this repository into a
`/docs` folder in the root of the project and then install the dependencies
using either `yarn` or `npm`.

```bash
$ git clone https://github.com/lm-commons/documentation-theme docs

$ cd docs
$ yarn
# or
$ npm install
```

## GitHub Workflows

There are two GitHub action workflows provided to provide Continuous Integration
and deployment to GitHub Pages.

Copy the GitHub action workflows from the `/docs/github-actions` folder to the 
`/.github/workflows` folder.

- `continuous-integration-docs.yml` performs a test build on push and pull
requests to the `/docs` folder.
- `deploy-to-gh-pages.yml` will run on manual dispatch to build and deploy to
the `gh_pages` branch.

## Configuration

Modify the file `config/index.js` to set package specific information:

```javascript
const packageConfig = {
  packageName: 'lmc-package-name',  // e.g. lmc-authentication
  title: 'your package name', // e.g. LMC Authentication
  tagline: 'A tagline', // e.g. Authentication for Mezzio applications
  projectName: 'project-name', // the repo name (e.g. lmc-authentication).
};
```
