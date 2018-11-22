# Arcanist linter extension for mypy

## Install

In order to use this extension this repository needs to be cloned
into your view. For example:

cd view
mkdir arcanist_linters
cd arcanist_linters
git clone ssh://git@phabricator.sourcevertex.net/diffusion/MYPYLINTER/mypy_linter.git

 view/
  arch_man/
  arch_dbs/
  ...
  arcanist_linters/
    mypy_linter/

## Use

Then use the linter by loading the module by adding the following to your .arcconfig:
  "load": [
    "../arcanist_linters/mypy_linter"
      ]

And then running the linter by adding the following to your .arclint:
   "mypy" : {
      "type" : "mypy",
      "include" : "(\\.py$)"
    }

