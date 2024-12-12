# Ceres
Primarily a submodule for Northeastern's drs-tk WordPress plugin.  [drs-tk](https://github.com/NEU-Libraries/drs-toolkit-wp-plugin)
Not yet ready for stable deployment.

## Usage within WordPress
1. In WordPress, clone a copy of the drs-tk plugin, using the `ceres-connection` branch. Double check with Patrick Murray-John about branches as development progresses.
2. Clone a copy of this repo into `libraries/Ceres`. Use `options-values` branch.Double check with Patrick Murray-John about branches as development progresses.
3. In the drs-tk plugin, run `git submodule init` and `git submodule update`. More complete and accurate info is in [Git's guide to submodules](https://git-scm.com/book/en/v2/Git-Tools-Submodules). `Ceres` will be in a detached HEAD state, so switch it back to the correct branch.

4. In Ceres, look in the `config/` directory. Copy `ceresSetup.php.wp` to `ceresSetup.php`
5. In Ceres, look in `data/rendererTemplates`. Copy `brc-leaflet.html.wp` to `brc-leaflet.html`
6. In Ceres, look in `assets/js/leaflet/brc/`. Copy `leaflet-brc-project.js.wp` to `leaflet-brc-project.js`
