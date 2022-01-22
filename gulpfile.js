const pkg = require( "./package.json" );
const gulp = require( "gulp" );
const wpPot = require( "wp-pot" );

// ### Make Pot
function makePot ( done ) {
  wpPot( {
    destFile: `./languages/${pkg.name}.pot`,
    domain: pkg.name,
    package: `${pkg.pluginName} ${pkg.version}`,
    src: "**/*.php",
  } );

  done();
}

// EXPORT methods
const build = gulp.parallel( makePot );

exports.build = build;
exports.default = build;