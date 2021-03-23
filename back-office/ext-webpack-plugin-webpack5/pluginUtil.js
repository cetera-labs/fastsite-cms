"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports._constructor = _constructor;
exports._thisCompilation = _thisCompilation;
exports._compilation = _compilation;
exports._afterCompile = _afterCompile;
exports._emit = _emit;
exports._done = _done;
exports._prepareForBuild = _prepareForBuild;
exports._buildExtBundle = _buildExtBundle;
exports._executeAsync = _executeAsync;
exports._toXtype = _toXtype;
exports._getApp = _getApp;
exports._getVersions = _getVersions;
exports.log = log;
exports.logh = logh;
exports.logv = logv;

function asyncGeneratorStep(gen, resolve, reject, _next, _throw, key, arg) { try { var info = gen[key](arg); var value = info.value; } catch (error) { reject(error); return; } if (info.done) { resolve(value); } else { Promise.resolve(value).then(_next, _throw); } }

function _asyncToGenerator(fn) { return function () { var self = this, args = arguments; return new Promise(function (resolve, reject) { var gen = fn.apply(self, args); function _next(value) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "next", value); } function _throw(err) { asyncGeneratorStep(gen, resolve, reject, _next, _throw, "throw", err); } _next(undefined); }); }; }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(source, true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(source).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

//**********
function _constructor(initialOptions) {
  const fs = require('fs');

  var vars = {};
  var options = {};

  try {
    if (initialOptions.framework == undefined) {
      vars.pluginErrors = [];
      vars.pluginErrors.push('webpack config: framework parameter on ext-webpack-plugin is not defined - values: react, angular, extjs, web-components');
      var result = {
        vars: vars
      };
      return result;
    }

    var framework = initialOptions.framework;
    var treeshake = initialOptions.treeshake;
    var verbose = initialOptions.verbose;

    const validateOptions = require('schema-utils');

    validateOptions(_getValidateOptions(), initialOptions, '');
    const rc = fs.existsSync(`.ext-${framework}rc`) && JSON.parse(fs.readFileSync(`.ext-${framework}rc`, 'utf-8')) || {};
    options = _objectSpread({}, _getDefaultOptions(), {}, initialOptions, {}, rc);
    vars = require(`./${framework}Util`)._getDefaultVars();
    vars.pluginName = 'ext-webpack-plugin';
    vars.app = _getApp();
    var pluginName = vars.pluginName;
    var app = vars.app;
    logv(verbose, 'FUNCTION _constructor');
    logv(verbose, `pluginName - ${pluginName}`);
    logv(verbose, `app - ${app}`);

    if (options.environment == 'production') {
      vars.production = true;
      options.browser = 'no';
      options.watch = 'no';
    } else {
      vars.production = false;
    }

    log(app, _getVersions(pluginName, framework)); //mjg added for angular cli build

    if (framework == 'angular' && options.intellishake == 'no' && vars.production == true && treeshake == 'yes') {
      vars.buildstep = '1 of 1';
      log(app, 'Starting production build for ' + framework);
    } else if (framework == 'react' || framework == 'extjs' || framework == 'web-components') {
      if (vars.production == true) {
        vars.buildstep = '1 of 1';
        log(app, 'Starting production build for ' + framework);
      } else {
        vars.buildstep = '1 of 1';
        log(app, 'Starting development build for ' + framework);
      }
    } else if (vars.production == true) {
      if (treeshake == 'yes') {
        vars.buildstep = '1 of 2';
        log(app, 'Starting production build for ' + framework + ' - ' + vars.buildstep);

        require(`./${framework}Util`)._toProd(vars, options);
      } else {
        vars.buildstep = '2 of 2';
        log(app, 'Continuing production build for ' + framework + ' - ' + vars.buildstep);
      }
    } else {
      vars.buildstep = '1 of 1';
      log(app, 'Starting development build for ' + framework);
    }

    logv(verbose, 'Building for ' + options.environment + ', ' + 'treeshake is ' + options.treeshake + ', ' + 'intellishake is ' + options.intellishake);
    var configObj = {
      vars: vars,
      options: options
    };
    return configObj;
  } catch (e) {
    throw '_constructor: ' + e.toString();
  }
} //**********


function _thisCompilation(compiler, compilation, vars, options) {
  try {
    var app = vars.app;
    var verbose = options.verbose;
    logv(verbose, 'FUNCTION _thisCompilation');
    logv(verbose, `options.script: ${options.script}`);
    logv(verbose, `buildstep: ${vars.buildstep}`);

    if (vars.buildstep === '1 of 1' || vars.buildstep === '1 of 2') {
      if (options.script != undefined && options.script != null && options.script != '') {
        log(app, `Started running ${options.script}`);
        runScript(options.script, function (err) {
          if (err) {
            throw err;
          }

          log(app, `Finished running ${options.script}`);
        });
      }
    }
  } catch (e) {
    throw '_thisCompilation: ' + e.toString();
  }
} //**********


function _compilation(compiler, compilation, vars, options) {
  try {
    var app = vars.app;
    var verbose = options.verbose;
    var framework = options.framework;
    logv(verbose, 'FUNCTION _compilation');

    if (framework != 'extjs') {
      if (options.treeshake === 'yes' && options.environment === 'production') {
        var extComponents = []; //mjg for 1 step build

        if (vars.buildstep == '1 of 1' && framework === 'angular' && options.intellishake == 'no') {
          extComponents = require(`./${framework}Util`)._getAllComponents(vars, options);
        }

        if (vars.buildstep == '1 of 2' || vars.buildstep == '1 of 1' && framework === 'web-components') {
          extComponents = require(`./${framework}Util`)._getAllComponents(vars, options);
        }

        compilation.hooks.succeedModule.tap(`ext-succeed-module`, module => {
          if (module.resource && !module.resource.match(/node_modules/)) {
            try {
              if (module.resource.match(/\.html$/) != null && module._source._value.toLowerCase().includes('doctype html') == false) {
                vars.deps = [...(vars.deps || []), ...require(`./${framework}Util`)._extractFromSource(module, options, compilation, extComponents)];
              } else {
                vars.deps = [...(vars.deps || []), ...require(`./${framework}Util`)._extractFromSource(module, options, compilation, extComponents)];
              }
            } catch (e) {
              console.log(e);
            }
          }
        });
      }

      if (vars.buildstep == '1 of 2') {
        compilation.hooks.finishModules.tap(`ext-finish-modules`, modules => {
          require(`./${framework}Util`)._writeFilesToProdFolder(vars, options);
        });
      }

      if (vars.buildstep == '1 of 1' || vars.buildstep == '2 of 2') {
        if (options.inject === 'yes') {
          compilation.hooks.htmlWebpackPluginBeforeHtmlGeneration.tap(`ext-html-generation`, data => {
            const path = require('path'); //var jsPath = path.join(vars.extPath, 'ext.js')
            //var cssPath = path.join(vars.extPath, 'ext.css')


            var jsPath = vars.extPath + '/' + 'ext.js';
            var cssPath = vars.extPath + '/' + 'ext.css';
            data.assets.js.unshift(jsPath);
            data.assets.css.unshift(cssPath);
            log(app, `Adding ${jsPath} and ${cssPath} to index.html`);
          });
        }
      }
    }
  } catch (e) {
    throw '_compilation: ' + e.toString(); //    logv(options.verbose,e)
    //    compilation.errors.push('_compilation: ' + e)
  }
} //**********


function _afterCompile(compiler, compilation, vars, options) {
  try {
    var app = vars.app;
    var verbose = options.verbose;
    var framework = options.framework;
    logv(verbose, 'FUNCTION _afterCompile');

    if (framework == 'extjs') {
      require(`./extjsUtil`)._afterCompile(compilation, vars, options);
    } else {
      logv(verbose, 'FUNCTION _afterCompile not run');
    }
  } catch (e) {
    throw '_afterCompile: ' + e.toString();
  }
} //**********


function _emit(_x, _x2, _x3, _x4, _x5) {
  return _emit2.apply(this, arguments);
} //**********


function _emit2() {
  _emit2 = _asyncToGenerator(
  /*#__PURE__*/
  regeneratorRuntime.mark(function _callee(compiler, compilation, vars, options, callback) {
    var path, app, verbose, emit, framework, outputPath, command, parms;
    return regeneratorRuntime.wrap(function _callee$(_context) {
      while (1) switch (_context.prev = _context.next) {
        case 0:
          _context.prev = 0;
          path = require('path');
          app = vars.app;
          verbose = options.verbose;
          emit = options.emit;
          framework = options.framework;
          logv(verbose, 'FUNCTION _emit');

          if (!(emit == 'yes')) {
            _context.next = 33;
            break;
          }

          if (!(vars.buildstep == '1 of 1' || vars.buildstep == '1 of 2')) {
            _context.next = 29;
            break;
          }

          outputPath = path.join(compiler.outputPath, vars.extPath);

          if (compiler.outputPath === '/' && compiler.options.devServer) {
            outputPath = path.join(compiler.options.devServer.contentBase, outputPath);
          }

          logv(verbose, 'outputPath: ' + outputPath);
          logv(verbose, 'framework: ' + framework);

          if (framework != 'extjs') {
            _prepareForBuild(app, vars, options, outputPath, compilation);
          }

          command = '';

          if (options.watch == 'yes' && vars.production == false) {
            command = 'watch';
          } else {
            command = 'build';
          }

          if (!(vars.rebuild == true)) {
            _context.next = 26;
            break;
          }

          parms = [];

          if (options.profile == undefined || options.profile == '' || options.profile == null) {
            if (command == 'build') {
              parms = ['app', command, options.environment];
            } else {
              parms = ['app', command, '--web-server', 'false', options.environment];
            }
          } else {
            if (command == 'build') {
              parms = ['app', command, options.profile, options.environment];
            } else {
              parms = ['app', command, '--web-server', 'false', options.profile, options.environment];
            }
          }

          if (!(vars.watchStarted == false)) {
            _context.next = 23;
            break;
          }

          _context.next = 22;
          return _buildExtBundle(app, compilation, outputPath, parms, vars, options);

        case 22:
          vars.watchStarted = true;

        case 23:
          callback();
          _context.next = 27;
          break;

        case 26:
          callback();

        case 27:
          _context.next = 31;
          break;

        case 29:
          logv(verbose, 'NOT running emit');
          callback();

        case 31:
          _context.next = 35;
          break;

        case 33:
          logv(verbose, 'emit is no');
          callback();

        case 35:
          _context.next = 41;
          break;

        case 37:
          _context.prev = 37;
          _context.t0 = _context["catch"](0);
          callback();
          throw '_emit: ' + _context.t0.toString();

        case 41:
        case "end":
          return _context.stop();
      }
    }, _callee, null, [[0, 37]]);
  }));
  return _emit2.apply(this, arguments);
}

function _done(stats, vars, options) {
  try {
    var verbose = options.verbose;
    var framework = options.framework;
    logv(verbose, 'FUNCTION _done');

    if (stats.compilation.errors && stats.compilation.errors.length) // && process.argv.indexOf('--watch') == -1)
      {
        var chalk = require('chalk');

        console.log(chalk.red('******************************************'));
        console.log(stats.compilation.errors[0]);
        console.log(chalk.red('******************************************'));
        process.exit(0);
      } //mjg refactor


    if (vars.production == true && options.treeshake == 'no' && framework == 'angular') {
      require(`./${options.framework}Util`)._toDev(vars, options);
    }

    try {
      if (options.browser == 'yes' && options.watch == 'yes' && vars.production == false) {
        if (vars.browserCount == 0) {
          var url = 'http://localhost:' + options.port;

          require('./pluginUtil').log(vars.app, `Opening browser at ${url}`);

          vars.browserCount++;

          const opn = require('opn');

          opn(url);
        }
      }
    } catch (e) {
      console.log(e);
    }

    if (vars.buildstep == '1 of 1') {
      if (vars.production == true) {
        require('./pluginUtil').log(vars.app, `Ending production build for ${framework}`);
      } else {
        require('./pluginUtil').log(vars.app, `Ending development build for ${framework}`);
      }
    }

    if (vars.buildstep == '2 of 2') {
      require('./pluginUtil').log(vars.app, `Ending production build for ${framework}`);
    }
  } catch (e) {
    //    require('./pluginUtil').logv(options.verbose,e)
    throw '_done: ' + e.toString();
  }
} //**********


function _prepareForBuild(app, vars, options, output, compilation) {
  try {
    var verbose = options.verbose;
    var packages = options.packages;
    var toolkit = options.toolkit;
    var theme = options.theme;
    logv(verbose, 'FUNCTION _prepareForBuild');

    const rimraf = require('rimraf');

    const mkdirp = require('mkdirp');

    const fsx = require('fs-extra');

    const fs = require('fs');

    const path = require('path');

    theme = theme || (toolkit === 'classic' ? 'theme-triton' : 'theme-material');
    logv(verbose, 'firstTime: ' + vars.firstTime);

    if (vars.firstTime) {
      rimraf.sync(output);
      mkdirp.sync(output);

      const buildXML = require('./artifacts').buildXML;

      const createAppJson = require('./artifacts').createAppJson;

      const createWorkspaceJson = require('./artifacts').createWorkspaceJson;

      const createJSDOMEnvironment = require('./artifacts').createJSDOMEnvironment;

      fs.writeFileSync(path.join(output, 'build.xml'), buildXML(vars.production, options, output), 'utf8');
      fs.writeFileSync(path.join(output, 'app.json'), createAppJson(theme, packages, toolkit, options, output), 'utf8');
      fs.writeFileSync(path.join(output, 'jsdom-environment.js'), createJSDOMEnvironment(options, output), 'utf8');
      fs.writeFileSync(path.join(output, 'workspace.json'), createWorkspaceJson(options, output), 'utf8');
      var framework = vars.framework; //because of a problem with colorpicker

      if (fs.existsSync(path.join(process.cwd(), `ext-${framework}/ux/`))) {
        var fromPath = path.join(process.cwd(), `ext-${framework}/ux/`);
        var toPath = path.join(output, 'ux');
        fsx.copySync(fromPath, toPath);
        log(app, 'Copying (ux) ' + fromPath.replace(process.cwd(), '') + ' to: ' + toPath.replace(process.cwd(), ''));
      }

      if (fs.existsSync(path.join(process.cwd(), `ext-${framework}/packages/`))) {
        var fromPath = path.join(process.cwd(), `ext-${framework}/packages/`);
        var toPath = path.join(output, 'packages');
        fsx.copySync(fromPath, toPath);
        log(app, 'Copying ' + fromPath.replace(process.cwd(), '') + ' to: ' + toPath.replace(process.cwd(), ''));
      }

      if (fs.existsSync(path.join(process.cwd(), `ext-${framework}/overrides/`))) {
        var fromPath = path.join(process.cwd(), `ext-${framework}/overrides/`);
        var toPath = path.join(output, 'overrides');
        fsx.copySync(fromPath, toPath);
        log(app, 'Copying ' + fromPath.replace(process.cwd(), '') + ' to: ' + toPath.replace(process.cwd(), ''));
      }

      if (fs.existsSync(path.join(process.cwd(), 'resources/'))) {
        var fromResources = path.join(process.cwd(), 'resources/');
        var toResources = path.join(output, '../resources');
        fsx.copySync(fromResources, toResources);
        log(app, 'Copying ' + fromResources.replace(process.cwd(), '') + ' to: ' + toResources.replace(process.cwd(), ''));
      }
    }

    vars.firstTime = false;
    var js = '';

    if (vars.production) {
      vars.deps = vars.deps.filter(function (value, index) {
        return vars.deps.indexOf(value) == index;
      });
      js = vars.deps.join(';\n');
    } else {
      js = `Ext.require(["Ext.*","Ext.data.TreeStore"])`;
    }

    if (vars.manifest === null || js !== vars.manifest) {
      vars.manifest = js + ';\nExt.require(["Ext.layout.*"]);\n';
      const manifest = path.join(output, 'manifest.js');
      fs.writeFileSync(manifest, vars.manifest, 'utf8');
      vars.rebuild = true;
      var bundleDir = output.replace(process.cwd(), '');

      if (bundleDir.trim() == '') {
        bundleDir = './';
      }

      log(app, 'Building Ext bundle at: ' + bundleDir);
    } else {
      vars.rebuild = false;
      log(app, 'Ext rebuild NOT needed');
    }
  } catch (e) {
    require('./pluginUtil').logv(options.verbose, e);

    compilation.errors.push('_prepareForBuild: ' + e);
  }
} //**********


function _buildExtBundle(app, compilation, outputPath, parms, vars, options) {
  //  try {
  var verbose = options.verbose;

  const fs = require('fs');

  logv(verbose, 'FUNCTION _buildExtBundle');
  let sencha;

  try {
    sencha = require('@sencha/cmd');
  } catch (e) {
    sencha = 'sencha';
  }

  if (fs.existsSync(sencha)) {
    logv(verbose, 'sencha folder exists');
  } else {
    logv(verbose, 'sencha folder DOES NOT exist');
  }

  return new Promise((resolve, reject) => {
    const onBuildDone = () => {
      logv(verbose, 'onBuildDone');
      resolve();
    };

    var opts = {
      cwd: outputPath,
      silent: true,
      stdio: 'pipe',
      encoding: 'utf-8'
    };

    _executeAsync(app, sencha, parms, opts, compilation, vars, options).then(function () {
      onBuildDone();
    }, function (reason) {
      reject(reason);
    });
  }); // }
  // catch(e) {
  //   console.log('e')
  //   require('./pluginUtil').logv(options.verbose,e)
  //   compilation.errors.push('_buildExtBundle: ' + e)
  //   callback()
  // }
} //**********


function _executeAsync(_x6, _x7, _x8, _x9, _x10, _x11, _x12) {
  return _executeAsync2.apply(this, arguments);
} //**********


function _executeAsync2() {
  _executeAsync2 = _asyncToGenerator(
  /*#__PURE__*/
  regeneratorRuntime.mark(function _callee2(app, command, parms, opts, compilation, vars, options) {
    var verbose, framework, DEFAULT_SUBSTRS, substrings, chalk, crossSpawn;
    return regeneratorRuntime.wrap(function _callee2$(_context2) {
      while (1) switch (_context2.prev = _context2.next) {
        case 0:
          //  try {
          verbose = options.verbose;
          framework = options.framework; //const DEFAULT_SUBSTRS = ['[INF] Loading', '[INF] Processing', '[LOG] Fashion build complete', '[ERR]', '[WRN]', "[INF] Server", "[INF] Writing", "[INF] Loading Build", "[INF] Waiting", "[LOG] Fashion waiting"];

          DEFAULT_SUBSTRS = ["[INF] xServer", '[INF] Loading', '[INF] Append', '[INF] Processing', '[INF] Processing Build', '[LOG] Fashion build complete', '[ERR]', '[WRN]', "[INF] Writing", "[INF] Loading Build", "[INF] Waiting", "[LOG] Fashion waiting"];
          substrings = DEFAULT_SUBSTRS;
          chalk = require('chalk');
          crossSpawn = require('cross-spawn');
          logv(verbose, 'FUNCTION _executeAsync');
          _context2.next = 9;
          return new Promise((resolve, reject) => {
            logv(verbose, `command - ${command}`);
            logv(verbose, `parms - ${parms}`);
            logv(verbose, `opts - ${JSON.stringify(opts)}`);
            let child = crossSpawn(command, parms, opts);
            child.on('close', (code, signal) => {
              logv(verbose, `on close: ` + code);

              if (code === 0) {
                resolve(0);
              } else {
                compilation.errors.push(new Error(code));
                resolve(0);
              }
            });
            child.on('error', error => {
              logv(verbose, `on error`);
              compilation.errors.push(error);
              resolve(0);
            });
            child.stdout.on('data', data => {
              var str = data.toString().replace(/\r?\n|\r/g, " ").trim();
              logv(verbose, `${str}`);

              if (data && data.toString().match(/Fashion waiting for changes\.\.\./)) {
                const fs = require('fs');

                var filename = process.cwd() + vars.touchFile;

                try {
                  var d = new Date().toLocaleString();
                  var data = fs.readFileSync(filename); // fs.writeFileSync(filename, '//' + d, 'utf8');

                  logv(app, `touching ${filename}`);
                } catch (e) {
                  logv(app, `NOT touching ${filename}`);
                }

                resolve(0);
              } else {
                if (substrings.some(function (v) {
                  return data.indexOf(v) >= 0;
                })) {
                  str = str.replace("[INF]", "");
                  str = str.replace("[LOG]", "");
                  str = str.replace(process.cwd(), '').trim();

                  if (str.includes("[ERR]")) {
                    compilation.errors.push(app + str.replace(/^\[ERR\] /gi, ''));
                    str = str.replace("[ERR]", `${chalk.red("[ERR]")}`);
                  }

                  log(app, str);
                }
              }
            });
            child.stderr.on('data', data => {
              logv(options, `error on close: ` + data);
              var str = data.toString().replace(/\r?\n|\r/g, " ").trim();
              var strJavaOpts = "Picked up _JAVA_OPTIONS";
              var includes = str.includes(strJavaOpts);

              if (!includes) {
                console.log(`${app} ${chalk.red("[ERR]")} ${str}`);
              }
            });
          });

        case 9:
        case "end":
          return _context2.stop();
      }
    }, _callee2);
  }));
  return _executeAsync2.apply(this, arguments);
}

function runScript(scriptPath, callback) {
  var childProcess = require('child_process'); // keep track of whether callback has been invoked to prevent multiple invocations


  var invoked = false;
  var process = childProcess.fork(scriptPath); // listen for errors as they may prevent the exit event from firing

  process.on('error', function (err) {
    if (invoked) return;
    invoked = true;
    callback(err);
  }); // execute the callback once the process has finished running

  process.on('exit', function (code) {
    if (invoked) return;
    invoked = true;
    var err = code === 0 ? null : new Error('exit code ' + code);
    callback(err);
  });
} //**********


function _toXtype(str) {
  return str.toLowerCase().replace(/_/g, '-');
} //**********


function _getApp() {
  var chalk = require('chalk');

  var prefix = ``;

  const platform = require('os').platform();

  if (platform == 'darwin') {
    prefix = `ℹ ｢ext｣:`;
  } else {
    prefix = `i [ext]:`;
  }

  return `${chalk.green(prefix)} `;
} //**********


function _getVersions(pluginName, frameworkName) {
  try {
    const path = require('path');

    const fs = require('fs');

    var v = {};
    var frameworkInfo = 'n/a';
    v.pluginVersion = 'n/a';
    v.extVersion = 'n/a';
    v.edition = 'n/a';
    v.cmdVersion = 'n/a';
    v.webpackVersion = 'n/a';
    var pluginPath = path.resolve(process.cwd(), 'node_modules/@sencha', pluginName);
    var pluginPkg = fs.existsSync(pluginPath + '/package.json') && JSON.parse(fs.readFileSync(pluginPath + '/package.json', 'utf-8')) || {};
    v.pluginVersion = pluginPkg.version;
    v._resolved = pluginPkg._resolved;

    if (v._resolved == undefined) {
      v.edition = `Commercial`;
    } else {
      if (-1 == v._resolved.indexOf('community')) {
        v.edition = `Commercial`;
      } else {
        v.edition = `Community`;
      }
    }

    var webpackPath = path.resolve(process.cwd(), 'node_modules/webpack');
    var webpackPkg = fs.existsSync(webpackPath + '/package.json') && JSON.parse(fs.readFileSync(webpackPath + '/package.json', 'utf-8')) || {};
    v.webpackVersion = webpackPkg.version;
    var extPath = path.resolve(process.cwd(), 'node_modules/@sencha/ext');
    var extPkg = fs.existsSync(extPath + '/package.json') && JSON.parse(fs.readFileSync(extPath + '/package.json', 'utf-8')) || {};
    v.extVersion = extPkg.sencha.version;
    var cmdPath = path.resolve(process.cwd(), `node_modules/@sencha/cmd`);
    var cmdPkg = fs.existsSync(cmdPath + '/package.json') && JSON.parse(fs.readFileSync(cmdPath + '/package.json', 'utf-8')) || {};
    v.cmdVersion = cmdPkg.version_full;

    if (v.cmdVersion == undefined) {
      var cmdPath = path.resolve(process.cwd(), `node_modules/@sencha/${pluginName}/node_modules/@sencha/cmd`);
      var cmdPkg = fs.existsSync(cmdPath + '/package.json') && JSON.parse(fs.readFileSync(cmdPath + '/package.json', 'utf-8')) || {};
      v.cmdVersion = cmdPkg.version_full;
    }

    if (frameworkName != undefined && frameworkName != 'extjs') {
      var frameworkPath = '';

      if (frameworkName == 'react') {
        frameworkPath = path.resolve(process.cwd(), 'node_modules/react');
      }

      if (frameworkName == 'angular') {
        frameworkPath = path.resolve(process.cwd(), 'node_modules/@angular/core');
      }

      var frameworkPkg = fs.existsSync(frameworkPath + '/package.json') && JSON.parse(fs.readFileSync(frameworkPath + '/package.json', 'utf-8')) || {};
      v.frameworkVersion = frameworkPkg.version;

      if (v.frameworkVersion == undefined) {
        frameworkInfo = ', ' + frameworkName;
      } else {
        frameworkInfo = ', ' + frameworkName + ' v' + v.frameworkVersion;
      }
    }

    return 'ext-webpack-plugin v' + v.pluginVersion + ', Ext JS v' + v.extVersion + ' ' + v.edition + ' Edition, Sencha Cmd v' + v.cmdVersion + ', webpack v' + v.webpackVersion + frameworkInfo;
  } catch (e) {
    return 'ext-webpack-plugin v' + v.pluginVersion + ', Ext JS v' + v.extVersion + ' ' + v.edition + ' Edition, Sencha Cmd v' + v.cmdVersion + ', webpack v' + v.webpackVersion + frameworkInfo;
  }
} //**********


function log(app, message) {
  var s = app + message;

  require('readline').cursorTo(process.stdout, 0);

  try {
    process.stdout.clearLine();
  } catch (e) {}

  process.stdout.write(s);
  process.stdout.write('\n');
} //**********


function logh(app, message) {
  var h = false;
  var s = app + message;

  if (h == true) {
    require('readline').cursorTo(process.stdout, 0);

    try {
      process.stdout.clearLine();
    } catch (e) {}

    process.stdout.write(s);
    process.stdout.write('\n');
  }
} //**********


function logv(verbose, s) {
  if (verbose == 'yes') {
    require('readline').cursorTo(process.stdout, 0);

    try {
      process.stdout.clearLine();
    } catch (e) {}

    process.stdout.write(`-verbose: ${s}`);
    process.stdout.write('\n');
  }
}

function _getValidateOptions() {
  return {
    "type": "object",
    "properties": {
      "framework": {
        "type": ["string"]
      },
      "toolkit": {
        "type": ["string"]
      },
      "theme": {
        "type": ["string"]
      },
      "emit": {
        "errorMessage": "should be 'yes' or 'no' string value (NOT true or false)",
        "type": ["string"]
      },
      "script": {
        "type": ["string"]
      },
      "port": {
        "type": ["integer"]
      },
      "packages": {
        "type": ["string", "array"]
      },
      "profile": {
        "type": ["string"]
      },
      "environment": {
        "errorMessage": "should be 'development' or 'production' string value",
        "type": ["string"]
      },
      "treeshake": {
        "errorMessage": "should be 'yes' or 'no' string value (NOT true or false)",
        "type": ["string"]
      },
      "browser": {
        "errorMessage": "should be 'yes' or 'no' string value (NOT true or false)",
        "type": ["string"]
      },
      "watch": {
        "errorMessage": "should be 'yes' or 'no' string value (NOT true or false)",
        "type": ["string"]
      },
      "verbose": {
        "errorMessage": "should be 'yes' or 'no' string value (NOT true or false)",
        "type": ["string"]
      },
      "inject": {
        "errorMessage": "should be 'yes' or 'no' string value (NOT true or false)",
        "type": ["string"]
      },
      "intellishake": {
        "errorMessage": "should be 'yes' or 'no' string value (NOT true or false)",
        "type": ["string"]
      }
    },
    "additionalProperties": false
  };
}

function _getDefaultOptions() {
  return {
    framework: 'extjs',
    toolkit: 'modern',
    theme: 'theme-material',
    emit: 'yes',
    script: null,
    port: 1962,
    packages: [],
    profile: '',
    environment: 'development',
    treeshake: 'no',
    browser: 'yes',
    watch: 'yes',
    verbose: 'no',
    inject: 'yes',
    intellishake: 'yes'
  };
}
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi4uL3NyYy9wbHVnaW5VdGlsLmpzIl0sIm5hbWVzIjpbIl9jb25zdHJ1Y3RvciIsImluaXRpYWxPcHRpb25zIiwiZnMiLCJyZXF1aXJlIiwidmFycyIsIm9wdGlvbnMiLCJmcmFtZXdvcmsiLCJ1bmRlZmluZWQiLCJwbHVnaW5FcnJvcnMiLCJwdXNoIiwicmVzdWx0IiwidHJlZXNoYWtlIiwidmVyYm9zZSIsInZhbGlkYXRlT3B0aW9ucyIsIl9nZXRWYWxpZGF0ZU9wdGlvbnMiLCJyYyIsImV4aXN0c1N5bmMiLCJKU09OIiwicGFyc2UiLCJyZWFkRmlsZVN5bmMiLCJfZ2V0RGVmYXVsdE9wdGlvbnMiLCJfZ2V0RGVmYXVsdFZhcnMiLCJwbHVnaW5OYW1lIiwiYXBwIiwiX2dldEFwcCIsImxvZ3YiLCJlbnZpcm9ubWVudCIsInByb2R1Y3Rpb24iLCJicm93c2VyIiwid2F0Y2giLCJsb2ciLCJfZ2V0VmVyc2lvbnMiLCJpbnRlbGxpc2hha2UiLCJidWlsZHN0ZXAiLCJfdG9Qcm9kIiwiY29uZmlnT2JqIiwiZSIsInRvU3RyaW5nIiwiX3RoaXNDb21waWxhdGlvbiIsImNvbXBpbGVyIiwiY29tcGlsYXRpb24iLCJzY3JpcHQiLCJydW5TY3JpcHQiLCJlcnIiLCJfY29tcGlsYXRpb24iLCJleHRDb21wb25lbnRzIiwiX2dldEFsbENvbXBvbmVudHMiLCJob29rcyIsInN1Y2NlZWRNb2R1bGUiLCJ0YXAiLCJtb2R1bGUiLCJyZXNvdXJjZSIsIm1hdGNoIiwiX3NvdXJjZSIsIl92YWx1ZSIsInRvTG93ZXJDYXNlIiwiaW5jbHVkZXMiLCJkZXBzIiwiX2V4dHJhY3RGcm9tU291cmNlIiwiY29uc29sZSIsImZpbmlzaE1vZHVsZXMiLCJtb2R1bGVzIiwiX3dyaXRlRmlsZXNUb1Byb2RGb2xkZXIiLCJpbmplY3QiLCJodG1sV2VicGFja1BsdWdpbkJlZm9yZUh0bWxHZW5lcmF0aW9uIiwiZGF0YSIsInBhdGgiLCJqc1BhdGgiLCJleHRQYXRoIiwiY3NzUGF0aCIsImFzc2V0cyIsImpzIiwidW5zaGlmdCIsImNzcyIsIl9hZnRlckNvbXBpbGUiLCJfZW1pdCIsImNhbGxiYWNrIiwiZW1pdCIsIm91dHB1dFBhdGgiLCJqb2luIiwiZGV2U2VydmVyIiwiY29udGVudEJhc2UiLCJfcHJlcGFyZUZvckJ1aWxkIiwiY29tbWFuZCIsInJlYnVpbGQiLCJwYXJtcyIsInByb2ZpbGUiLCJ3YXRjaFN0YXJ0ZWQiLCJfYnVpbGRFeHRCdW5kbGUiLCJfZG9uZSIsInN0YXRzIiwiZXJyb3JzIiwibGVuZ3RoIiwiY2hhbGsiLCJyZWQiLCJwcm9jZXNzIiwiZXhpdCIsIl90b0RldiIsImJyb3dzZXJDb3VudCIsInVybCIsInBvcnQiLCJvcG4iLCJvdXRwdXQiLCJwYWNrYWdlcyIsInRvb2xraXQiLCJ0aGVtZSIsInJpbXJhZiIsIm1rZGlycCIsImZzeCIsImZpcnN0VGltZSIsInN5bmMiLCJidWlsZFhNTCIsImNyZWF0ZUFwcEpzb24iLCJjcmVhdGVXb3Jrc3BhY2VKc29uIiwiY3JlYXRlSlNET01FbnZpcm9ubWVudCIsIndyaXRlRmlsZVN5bmMiLCJjd2QiLCJmcm9tUGF0aCIsInRvUGF0aCIsImNvcHlTeW5jIiwicmVwbGFjZSIsImZyb21SZXNvdXJjZXMiLCJ0b1Jlc291cmNlcyIsImZpbHRlciIsInZhbHVlIiwiaW5kZXgiLCJpbmRleE9mIiwibWFuaWZlc3QiLCJidW5kbGVEaXIiLCJ0cmltIiwic2VuY2hhIiwiUHJvbWlzZSIsInJlc29sdmUiLCJyZWplY3QiLCJvbkJ1aWxkRG9uZSIsIm9wdHMiLCJzaWxlbnQiLCJzdGRpbyIsImVuY29kaW5nIiwiX2V4ZWN1dGVBc3luYyIsInRoZW4iLCJyZWFzb24iLCJERUZBVUxUX1NVQlNUUlMiLCJzdWJzdHJpbmdzIiwiY3Jvc3NTcGF3biIsInN0cmluZ2lmeSIsImNoaWxkIiwib24iLCJjb2RlIiwic2lnbmFsIiwiRXJyb3IiLCJlcnJvciIsInN0ZG91dCIsInN0ciIsImZpbGVuYW1lIiwidG91Y2hGaWxlIiwiZCIsIkRhdGUiLCJ0b0xvY2FsZVN0cmluZyIsInNvbWUiLCJ2Iiwic3RkZXJyIiwic3RySmF2YU9wdHMiLCJzY3JpcHRQYXRoIiwiY2hpbGRQcm9jZXNzIiwiaW52b2tlZCIsImZvcmsiLCJfdG9YdHlwZSIsInByZWZpeCIsInBsYXRmb3JtIiwiZ3JlZW4iLCJmcmFtZXdvcmtOYW1lIiwiZnJhbWV3b3JrSW5mbyIsInBsdWdpblZlcnNpb24iLCJleHRWZXJzaW9uIiwiZWRpdGlvbiIsImNtZFZlcnNpb24iLCJ3ZWJwYWNrVmVyc2lvbiIsInBsdWdpblBhdGgiLCJwbHVnaW5Qa2ciLCJ2ZXJzaW9uIiwiX3Jlc29sdmVkIiwid2VicGFja1BhdGgiLCJ3ZWJwYWNrUGtnIiwiZXh0UGtnIiwiY21kUGF0aCIsImNtZFBrZyIsInZlcnNpb25fZnVsbCIsImZyYW1ld29ya1BhdGgiLCJmcmFtZXdvcmtQa2ciLCJmcmFtZXdvcmtWZXJzaW9uIiwibWVzc2FnZSIsInMiLCJjdXJzb3JUbyIsImNsZWFyTGluZSIsIndyaXRlIiwibG9naCIsImgiXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7QUFDQTtBQUNPLFNBQVNBLFlBQVQsQ0FBc0JDLGNBQXRCLEVBQXNDO0FBQzNDLFFBQU1DLEVBQUUsR0FBR0MsT0FBTyxDQUFDLElBQUQsQ0FBbEI7O0FBQ0EsTUFBSUMsSUFBSSxHQUFHLEVBQVg7QUFDQSxNQUFJQyxPQUFPLEdBQUcsRUFBZDs7QUFDQSxNQUFJO0FBQ0YsUUFBSUosY0FBYyxDQUFDSyxTQUFmLElBQTRCQyxTQUFoQyxFQUEyQztBQUN6Q0gsTUFBQUEsSUFBSSxDQUFDSSxZQUFMLEdBQW9CLEVBQXBCO0FBQ0FKLE1BQUFBLElBQUksQ0FBQ0ksWUFBTCxDQUFrQkMsSUFBbEIsQ0FBdUIsMEhBQXZCO0FBQ0EsVUFBSUMsTUFBTSxHQUFHO0FBQUVOLFFBQUFBLElBQUksRUFBRUE7QUFBUixPQUFiO0FBQ0EsYUFBT00sTUFBUDtBQUNEOztBQUNELFFBQUlKLFNBQVMsR0FBR0wsY0FBYyxDQUFDSyxTQUEv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Q0xkLElBQUFBLFNBQVMsRUFBRSxPQUROO0FBRUxrRyxJQUFBQSxPQUFPLEVBQUUsUUFGSjtBQUdMQyxJQUFBQSxLQUFLLEVBQUUsZ0JBSEY7QUFJTDVCLElBQUFBLElBQUksRUFBRSxLQUpEO0FBS0xwQyxJQUFBQSxNQUFNLEVBQUUsSUFMSDtBQU1MMkQsSUFBQUEsSUFBSSxFQUFFLElBTkQ7QUFPTEcsSUFBQUEsUUFBUSxFQUFFLEVBUEw7QUFTTGpCLElBQUFBLE9BQU8sRUFBRSxFQVRKO0FBVUw1RCxJQUFBQSxXQUFXLEVBQUUsYUFWUjtBQVdMZixJQUFBQSxTQUFTLEVBQUUsSUFYTjtBQVlMaUIsSUFBQUEsT0FBTyxFQUFFLEtBWko7QUFhTEMsSUFBQUEsS0FBSyxFQUFFLEtBYkY7QUFjTGpCLElBQUFBLE9BQU8sRUFBRSxJQWRKO0FBZUxtRCxJQUFBQSxNQUFNLEVBQUUsS0FmSDtBQWdCTC9CLElBQUFBLFlBQVksRUFBRTtBQWhCVCxHQUFQO0FBa0JEIiwic291cmNlc0NvbnRlbnQiOlsiXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBfY29uc3RydWN0b3IoaW5pdGlhbE9wdGlvbnMpIHtcbiAgY29uc3QgZnMgPSByZXF1aXJlKCdmcycpXG4gIHZhciB2YXJzID0ge31cbiAgdmFyIG9wdGlvbnMgPSB7fVxuICB0cnkge1xuICAgIGlmIChpbml0aWFsT3B0aW9ucy5mcmFtZXdvcmsgPT0gdW5kZWZpbmVkKSB7XG4gICAgICB2YXJzLnBsdWdpbkVycm9ycyA9IFtdXG4gICAgICB2YXJzLnBsdWdpbkVycm9ycy5wdXNoKCd3ZWJwYWNrIGNvbmZpZzogZnJhbWV3b3JrIHBhcmFtZXRlciBvbiBleHQtd2VicGFjay1wbHVnaW4gaXMgbm90IGRlZmluZWQgLSB2YWx1ZXM6IHJlYWN0LCBhbmd1bGFyLCBleHRqcywgd2ViLWNvbXBvbmVudHMnKVxuICAgICAgdmFyIHJlc3VsdCA9IHsgdmFyczogdmFycyB9O1xuICAgICAgcmV0dXJuIHJlc3VsdDtcbiAgICB9XG4gICAgdmFyIGZyYW1ld29yayA9IGluaXRpYWxPcHRpb25zLmZyYW1ld29ya1xuICAgIHZhciB0cmVlc2hha2UgPSBpbml0aWFsT3B0aW9ucy50cmVlc2hha2VcbiAgICB2YXIgdmVyYm9zZSA9IGluaXRpYWxPcHRpb25zLnZlcmJvc2VcblxuICAgIGNvbnN0IHZhbGlkYXRlT3B0aW9ucyA9IHJlcXVpcmUoJ3NjaGVtYS11dGlscycpXG4gICAgdmFsaWRhdGVPcHRpb25zKF9nZXRWYWxpZGF0ZU9wdGlvbnMoKSwgaW5pdGlhbE9wdGlvbnMsICcnKVxuXG4gICAgY29uc3QgcmMgPSAoZnMuZXhpc3RzU3luYyhgLmV4dC0ke2ZyYW1ld29ya31yY2ApICYmIEpTT04ucGFyc2UoZnMucmVhZEZpbGVTeW5jKGAuZXh0LSR7ZnJhbWV3b3JrfXJjYCwgJ3V0Zi04JykpIHx8IHt9KVxuICAgIG9wdGlvbnMgPSB7IC4uLl9nZXREZWZhdWx0T3B0aW9ucygpLCAuLi5pbml0aWFsT3B0aW9ucywgLi4ucmMgfVxuXG4gICAgdmFycyA9IHJlcXVpcmUoYC4vJHtmcmFtZXdvcmt9VXRpbGApLl9nZXREZWZhdWx0VmFycygpXG4gICAgdmFycy5wbHVnaW5OYW1lID0gJ2V4dC13ZWJwYWNrLXBsdWdpbidcbiAgICB2YXJzLmFwcCA9IF9nZXRBcHAoKVxuICAgIHZhciBwbHVnaW5OYW1lID0gdmFycy5wbHVnaW5OYW1lXG4gICAgdmFyIGFwcCA9IHZhcnMuYXBwXG5cbiAgICBsb2d2KHZlcmJvc2UsICdGVU5DVElPTiBfY29uc3RydWN0b3InKVxuICAgIGxvZ3YodmVyYm9zZSwgYHBsdWdpbk5hbWUgLSAke3BsdWdpbk5hbWV9YClcbiAgICBsb2d2KHZlcmJvc2UsIGBhcHAgLSAke2FwcH1gKVxuXG4gICAgaWYgKG9wdGlvbnMuZW52aXJvbm1lbnQgPT0gJ3Byb2R1Y3Rpb24nKSB7XG4gICAgICB2YXJzLnByb2R1Y3Rpb24gPSB0cnVlXG4gICAgICBvcHRpb25zLmJyb3dzZXIgPSAnbm8nXG4gICAgICBvcHRpb25zLndhdGNoID0gJ25vJ1xuICAgIH1cbiAgICBlbHNlIHtcbiAgICAgIHZhcnMucHJvZHVjdGlvbiA9IGZhbHNlXG4gICAgfVxuXG4gICAgbG9nKGFwcCwgX2dldFZlcnNpb25zKHBsdWdpbk5hbWUsIGZyYW1ld29yaykpXG5cbiAgICAvL21qZyBhZGRlZCBmb3IgYW5ndWxhciBjbGkgYnVpbGRcbiAgICBpZiAoZnJhbWV3b3JrID09ICdhbmd1bGFyJyAmJlxuICAgICAgICBvcHRpb25zLmludGVsbGlzaGFrZSA9PSAnbm8nICYmXG4gICAgICAgIHZhcnMucHJvZHVjdGlvbiA9PSB0cnVlXG4gICAgICAgICYmIHRyZWVzaGFrZSA9PSAneWVzJykge1xuICAgICAgICAgICAgdmFycy5idWlsZHN0ZXAgPSAnMSBvZiAxJztcbiAgICAgICAgICAgIGxvZyhhcHAsICdTdGFydGluZyBwcm9kdWN0aW9uIGJ1aWxkIGZvciAnICsgZnJhbWV3b3JrKTtcbiAgICB9XG5cbiAgICBlbHNlIGlmIChmcmFtZXdvcmsgPT0gJ3JlYWN0JyB8fCBmcmFtZXdvcmsgPT0gJ2V4dGpzJyB8fCBmcmFtZXdvcmsgPT0gJ3dlYi1jb21wb25lbnRzJykge1xuICAgICAgaWYgKHZhcnMucHJvZHVjdGlvbiA9PSB0cnVlKSB7XG4gICAgICAgIHZhcnMuYnVpbGRzdGVwID0gJzEgb2YgMSdcbiAgICAgICAgbG9nKGFwcCwgJ1N0YXJ0aW5nIHByb2R1Y3Rpb24gYnVpbGQgZm9yICcgKyBmcmFtZXdvcmspXG4gICAgICB9XG4gICAgICBlbHNlIHtcbiAgICAgICAgdmFycy5idWlsZHN0ZXAgPSAnMSBvZiAxJ1xuICAgICAgICBsb2coYXBwLCAnU3RhcnRpbmcgZGV2ZWxvcG1lbnQgYnVpbGQgZm9yICcgKyBmcmFtZXdvcmspXG4gICAgICB9XG4gICAgfVxuICAgIGVsc2UgaWYgKHZhcnMucHJvZHVjdGlvbiA9PSB0cnVlKSB7XG4gICAgICBpZiAodHJlZXNoYWtlID09ICd5ZXMnKSB7XG4gICAgICAgIHZhcnMuYnVpbGRzdGVwID0gJzEgb2YgMidcbiAgICAgICAgbG9nKGFwcCwgJ1N0YXJ0aW5nIHByb2R1Y3Rpb24gYnVpbGQgZm9yICcgKyBmcmFtZXdvcmsgKyAnIC0gJyArIHZhcnMuYnVpbGRzdGVwKVxuICAgICAgICByZXF1aXJlKGAuLyR7ZnJhbWV3b3JrfVV0aWxgKS5fdG9Qcm9kKHZhcnMsIG9wdGlvbnMpXG4gICAgICB9XG4gICAgICBlbHNlIHtcbiAgICAgICAgdmFycy5idWlsZHN0ZXAgPSAnMiBvZiAyJ1xuICAgICAgICBsb2coYXBwLCAnQ29udGludWluZyBwcm9kdWN0aW9uIGJ1aWxkIGZvciAnICsgZnJhbWV3b3JrICsgJyAtICcgKyB2YXJzLmJ1aWxkc3RlcClcbiAgICAgIH1cbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICB2YXJzLmJ1aWxkc3RlcCA9ICcxIG9mIDEnXG4gICAgICBsb2coYXBwLCAnU3RhcnRpbmcgZGV2ZWxvcG1lbnQgYnVpbGQgZm9yICcgKyBmcmFtZXdvcmspXG4gICAgfVxuICAgIGxvZ3YodmVyYm9zZSwgJ0J1aWxkaW5nIGZvciAnICsgb3B0aW9ucy5lbnZpcm9ubWVudCArICcsICcgKyAndHJlZXNoYWtlIGlzICcgKyBvcHRpb25zLnRyZWVzaGFrZSsgJywgJyArICdpbnRlbGxpc2hha2UgaXMgJyArIG9wdGlvbnMuaW50ZWxsaXNoYWtlKVxuXG4gICAgdmFyIGNvbmZpZ09iaiA9IHsgdmFyczogdmFycywgb3B0aW9uczogb3B0aW9ucyB9O1xuICAgIHJldHVybiBjb25maWdPYmo7XG4gIH1cbiAgY2F0Y2ggKGUpIHtcbiAgICB0aHJvdyAnX2NvbnN0cnVjdG9yOiAnICsgZS50b1N0cmluZygpXG4gIH1cbn1cblxuLy8qKioqKioqKioqXG5leHBvcnQgZnVuY3Rpb24gX3RoaXNDb21waWxhdGlvbihjb21waWxlciwgY29tcGlsYXRpb24sIHZhcnMsIG9wdGlvbnMpIHtcbiAgdHJ5IHtcbiAgICB2YXIgYXBwID0gdmFycy5hcHBcbiAgICB2YXIgdmVyYm9zZSA9IG9wdGlvbnMudmVyYm9zZVxuICAgIGxvZ3YodmVyYm9zZSwgJ0ZVTkNUSU9OIF90aGlzQ29tcGlsYXRpb24nKVxuICAgIGxvZ3YodmVyYm9zZSwgYG9wdGlvbnMuc2NyaXB0OiAke29wdGlvbnMuc2NyaXB0IH1gKVxuICAgIGxvZ3YodmVyYm9zZSwgYGJ1aWxkc3RlcDogJHt2YXJzLmJ1aWxkc3RlcH1gKVxuXG4gICAgaWYgKHZhcnMuYnVpbGRzdGVwID09PSAnMSBvZiAxJyB8fCB2YXJzLmJ1aWxkc3RlcCA9PT0gJzEgb2YgMicpIHtcbiAgICAgIGlmIChvcHRpb25zLnNjcmlwdCAhPSB1bmRlZmluZWQgJiYgb3B0aW9ucy5zY3JpcHQgIT0gbnVsbCAmJiBvcHRpb25zLnNjcmlwdCAhPSAnJykge1xuICAgICAgICBsb2coYXBwLCBgU3RhcnRlZCBydW5uaW5nICR7b3B0aW9ucy5zY3JpcHR9YClcbiAgICAgICAgcnVuU2NyaXB0KG9wdGlvbnMuc2NyaXB0LCBmdW5jdGlvbiAoZXJyKSB7XG4gICAgICAgICAgaWYgKGVycikge1xuICAgICAgICAgICAgdGhyb3cgZXJyO1xuICAgICAgICAgIH1cbiAgICAgICAgICBsb2coYXBwLCBgRmluaXNoZWQgcnVubmluZyAke29wdGlvbnMuc2NyaXB0fWApXG4gICAgICAgIH0pO1xuICAgICAgfVxuICAgIH1cbiAgfVxuICBjYXRjaChlKSB7XG4gICAgdGhyb3cgJ190aGlzQ29tcGlsYXRpb246ICcgKyBlLnRvU3RyaW5nKClcbiAgfVxufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBfY29tcGlsYXRpb24oY29tcGlsZXIsIGNvbXBpbGF0aW9uLCB2YXJzLCBvcHRpb25zKSB7XG4gIHRyeSB7XG4gICAgdmFyIGFwcCA9IHZhcnMuYXBwXG4gICAgdmFyIHZlcmJvc2UgPSBvcHRpb25zLnZlcmJvc2VcbiAgICB2YXIgZnJhbWV3b3JrID0gb3B0aW9ucy5mcmFtZXdvcmtcbiAgICBsb2d2KHZlcmJvc2UsICdGVU5DVElPTiBfY29tcGlsYXRpb24nKVxuXG4gICAgaWYgKGZyYW1ld29yayAhPSAnZXh0anMnKSB7XG4gICAgICBpZiAob3B0aW9ucy50cmVlc2hha2UgPT09ICd5ZXMnICYmIG9wdGlvbnMuZW52aXJvbm1lbnQgPT09ICdwcm9kdWN0aW9uJykge1xuICAgICAgICB2YXIgZXh0Q29tcG9uZW50cyA9IFtdO1xuXG4gICAgICAgIC8vbWpnIGZvciAxIHN0ZXAgYnVpbGRcbiAgICAgICAgaWYgKHZhcnMuYnVpbGRzdGVwID09ICcxIG9mIDEnICYmIGZyYW1ld29yayA9PT0gJ2FuZ3VsYXInICYmIG9wdGlvbnMuaW50ZWxsaXNoYWtlID09ICdubycpIHtcbiAgICAgICAgICAgIGV4dENvbXBvbmVudHMgPSByZXF1aXJlKGAuLyR7ZnJhbWV3b3JrfVV0aWxgKS5fZ2V0QWxsQ29tcG9uZW50cyh2YXJzLCBvcHRpb25zKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICh2YXJzLmJ1aWxkc3RlcCA9PSAnMSBvZiAyJyB8fCAodmFycy5idWlsZHN0ZXAgPT0gJzEgb2YgMScgJiYgZnJhbWV3b3JrID09PSAnd2ViLWNvbXBvbmVudHMnKSkge1xuICAgICAgICAgIGV4dENvbXBvbmVudHMgPSByZXF1aXJlKGAuLyR7ZnJhbWV3b3JrfVV0aWxgKS5fZ2V0QWxsQ29tcG9uZW50cyh2YXJzLCBvcHRpb25zKVxuICAgICAgICB9XG4gICAgICAgIGNvbXBpbGF0aW9uLmhvb2tzLnN1Y2NlZWRNb2R1bGUudGFwKGBleHQtc3VjY2VlZC1tb2R1bGVgLCBtb2R1bGUgPT4ge1xuICAgICAgICAgIGlmIChtb2R1bGUucmVzb3VyY2UgJiYgIW1vZHVsZS5yZXNvdXJjZS5tYXRjaCgvbm9kZV9tb2R1bGVzLykpIHtcbiAgICAgICAgICAgIHRyeSB7XG4gICAgICAgICAgICAgICAgaWYgKG1vZHVsZS5yZXNvdXJjZS5tYXRjaCgvXFwuaHRtbCQvKSAhPSBudWxsXG4gICAgICAgICAgICAgICAgJiYgbW9kdWxlLl9zb3VyY2UuX3ZhbHVlLnRvTG93ZXJDYXNlKCkuaW5jbHVkZXMoJ2RvY3R5cGUgaHRtbCcpID09IGZhbHNlXG4gICAgICAgICAgICAgICAgKSB7XG4gICAgICAgICAgICAgICAgICAgIHZhcnMuZGVwcyA9IFtcbiAgICAgICAgICAgICAgICAgICAgICAgIC4uLih2YXJzLmRlcHMgfHwgW10pLFxuICAgICAgICAgICAgICAgICAgICAgICAgLi4ucmVxdWlyZShgLi8ke2ZyYW1ld29ya31VdGlsYCkuX2V4dHJhY3RGcm9tU291cmNlKG1vZHVsZSwgb3B0aW9ucywgY29tcGlsYXRpb24sIGV4dENvbXBvbmVudHMpXVxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIHZhcnMuZGVwcyA9IFtcbiAgICAgICAgICAgICAgICAgICAgICAgIC4uLih2YXJzLmRlcHMgfHwgW10pLFxuICAgICAgICAgICAgICAgICAgICAgICAgLi4ucmVxdWlyZShgLi8ke2ZyYW1ld29ya31VdGlsYCkuX2V4dHJhY3RGcm9tU291cmNlKG1vZHVsZSwgb3B0aW9ucywgY29tcGlsYXRpb24sIGV4dENvbXBvbmVudHMpXVxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICBjYXRjaChlKSB7XG4gICAgICAgICAgICAgICAgY29uc29sZS5sb2coZSlcbiAgICAgICAgICAgIH1cbiAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgICAgfVxuICAgICAgaWYgKHZhcnMuYnVpbGRzdGVwID09ICcxIG9mIDInKSB7XG4gICAgICAgIGNvbXBpbGF0aW9uLmhvb2tzLmZpbmlzaE1vZHVsZXMudGFwKGBleHQtZmluaXNoLW1vZHVsZXNgLCBtb2R1bGVzID0+IHtcbiAgICAgICAgICByZXF1aXJlKGAuLyR7ZnJhbWV3b3JrfVV0aWxgKS5fd3JpdGVGaWxlc1RvUHJvZEZvbGRlcih2YXJzLCBvcHRpb25zKVxuICAgICAgICB9KVxuICAgICAgfVxuICAgICAgaWYgKHZhcnMuYnVpbGRzdGVwID09ICcxIG9mIDEnIHx8IHZhcnMuYnVpbGRzdGVwID09ICcyIG9mIDInKSB7XG4gICAgICAgIGlmIChvcHRpb25zLmluamVjdCA9PT0gJ3llcycpIHtcbiAgICAgICAgICBjb21waWxhdGlvbi5ob29rcy5odG1sV2VicGFja1BsdWdpbkJlZm9yZUh0bWxHZW5lcmF0aW9uLnRhcChgZXh0LWh0bWwtZ2VuZXJhdGlvbmAsKGRhdGEpID0+IHtcbiAgICAgICAgICAgIGNvbnN0IHBhdGggPSByZXF1aXJlKCdwYXRoJylcbiAgICAgICAgICAgIC8vdmFyIGpzUGF0aCA9IHBhdGguam9pbih2YXJzLmV4dFBhdGgsICdleHQuanMnKVxuICAgICAgICAgICAgLy92YXIgY3NzUGF0aCA9IHBhdGguam9pbih2YXJzLmV4dFBhdGgsICdleHQuY3NzJylcbiAgICAgICAgICAgIHZhciBqc1BhdGggPSB2YXJzLmV4dFBhdGggKyAnLycgKyAgJ2V4dC5qcyc7XG4gICAgICAgICAgICB2YXIgY3NzUGF0aCA9IHZhcnMuZXh0UGF0aCArICcvJyArICdleHQuY3NzJztcbiAgICAgICAgICAgIGRhdGEuYXNzZXRzLmpzLnVuc2hpZnQoanNQYXRoKVxuICAgICAgICAgICAgZGF0YS5hc3NldHMuY3NzLnVuc2hpZnQoY3NzUGF0aClcbiAgICAgICAgICAgIGxvZyhhcHAsIGBBZGRpbmcgJHtqc1BhdGh9IGFuZCAke2Nzc1BhdGh9IHRvIGluZGV4Lmh0bWxgKVxuICAgICAgICAgIH0pXG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cbiAgY2F0Y2goZSkge1xuICAgIHRocm93ICdfY29tcGlsYXRpb246ICcgKyBlLnRvU3RyaW5nKClcbi8vICAgIGxvZ3Yob3B0aW9ucy52ZXJib3NlLGUpXG4vLyAgICBjb21waWxhdGlvbi5lcnJvcnMucHVzaCgnX2NvbXBpbGF0aW9uOiAnICsgZSlcbiAgfVxufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBfYWZ0ZXJDb21waWxlKGNvbXBpbGVyLCBjb21waWxhdGlvbiwgdmFycywgb3B0aW9ucykge1xuICB0cnkge1xuICAgIHZhciBhcHAgPSB2YXJzLmFwcFxuICAgIHZhciB2ZXJib3NlID0gb3B0aW9ucy52ZXJib3NlXG4gICAgdmFyIGZyYW1ld29yayA9IG9wdGlvbnMuZnJhbWV3b3JrXG4gICAgbG9ndih2ZXJib3NlLCAnRlVOQ1RJT04gX2FmdGVyQ29tcGlsZScpXG4gICAgaWYgKGZyYW1ld29yayA9PSAnZXh0anMnKSB7XG4gICAgICByZXF1aXJlKGAuL2V4dGpzVXRpbGApLl9hZnRlckNvbXBpbGUoY29tcGlsYXRpb24sIHZhcnMsIG9wdGlvbnMpXG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgbG9ndih2ZXJib3NlLCAnRlVOQ1RJT04gX2FmdGVyQ29tcGlsZSBub3QgcnVuJylcbiAgICB9XG4gIH1cbiAgY2F0Y2goZSkge1xuICAgIHRocm93ICdfYWZ0ZXJDb21waWxlOiAnICsgZS50b1N0cmluZygpXG4gIH1cbn1cblxuLy8qKioqKioqKioqXG5leHBvcnQgYXN5bmMgZnVuY3Rpb24gX2VtaXQoY29tcGlsZXIsIGNvbXBpbGF0aW9uLCB2YXJzLCBvcHRpb25zLCBjYWxsYmFjaykge1xuICB0cnkge1xuICAgIGNvbnN0IHBhdGggPSByZXF1aXJlKCdwYXRoJylcbiAgICB2YXIgYXBwID0gdmFycy5hcHBcbiAgICB2YXIgdmVyYm9zZSA9IG9wdGlvbnMudmVyYm9zZVxuICAgIHZhciBlbWl0ID0gb3B0aW9ucy5lbWl0XG4gICAgdmFyIGZyYW1ld29yayA9IG9wdGlvbnMuZnJhbWV3b3JrXG4gICAgbG9ndih2ZXJib3NlLCdGVU5DVElPTiBfZW1pdCcpXG4gICAgaWYgKGVtaXQgPT0gJ3llcycpIHtcbiAgICAgIGlmICh2YXJzLmJ1aWxkc3RlcCA9PSAnMSBvZiAxJyB8fCB2YXJzLmJ1aWxkc3RlcCA9PSAnMSBvZiAyJykge1xuICAgICAgICBsZXQgb3V0cHV0UGF0aCA9IHBhdGguam9pbihjb21waWxlci5vdXRwdXRQYXRoLHZhcnMuZXh0UGF0aClcbiAgICAgICAgaWYgKGNvbXBpbGVyLm91dHB1dFBhdGggPT09ICcvJyAmJiBjb21waWxlci5vcHRpb25zLmRldlNlcnZlcikge1xuICAgICAgICAgIG91dHB1dFBhdGggPSBwYXRoLmpvaW4oY29tcGlsZXIub3B0aW9ucy5kZXZTZXJ2ZXIuY29udGVudEJhc2UsIG91dHB1dFBhdGgpXG4gICAgICAgIH1cbiAgICAgICAgbG9ndih2ZXJib3NlLCdvdXRwdXRQYXRoOiAnICsgb3V0cHV0UGF0aClcbiAgICAgICAgbG9ndih2ZXJib3NlLCdmcmFtZXdvcms6ICcgKyBmcmFtZXdvcmspXG4gICAgICAgIGlmIChmcmFtZXdvcmsgIT0gJ2V4dGpzJykge1xuICAgICAgICAgIF9wcmVwYXJlRm9yQnVpbGQoYXBwLCB2YXJzLCBvcHRpb25zLCBvdXRwdXRQYXRoLCBjb21waWxhdGlvbilcbiAgICAgICAgfVxuICAgICAgICB2YXIgY29tbWFuZCA9ICcnXG4gICAgICAgIGlmIChvcHRpb25zLndhdGNoID09ICd5ZXMnICYmIHZhcnMucHJvZHVjdGlvbiA9PSBmYWxzZSlcbiAgICAgICAgICB7Y29tbWFuZCA9ICd3YXRjaCd9XG4gICAgICAgIGVsc2VcbiAgICAgICAgICB7Y29tbWFuZCA9ICdidWlsZCd9XG4gICAgICAgIGlmICh2YXJzLnJlYnVpbGQgPT0gdHJ1ZSkge1xuICAgICAgICAgIHZhciBwYXJtcyA9IFtdXG4gICAgICAgICAgaWYgKG9wdGlvbnMucHJvZmlsZSA9PSB1bmRlZmluZWQgfHwgb3B0aW9ucy5wcm9maWxlID09ICcnIHx8IG9wdGlvbnMucHJvZmlsZSA9PSBudWxsKSB7XG4gICAgICAgICAgICBpZiAoY29tbWFuZCA9PSAnYnVpbGQnKVxuICAgICAgICAgICAgICB7IHBhcm1zID0gWydhcHAnLCBjb21tYW5kLCBvcHRpb25zLmVudmlyb25tZW50XSB9XG4gICAgICAgICAgICBlbHNlXG4gICAgICAgICAgICAgIHsgcGFybXMgPSBbJ2FwcCcsIGNvbW1hbmQsICctLXdlYi1zZXJ2ZXInLCAnZmFsc2UnLCBvcHRpb25zLmVudmlyb25tZW50XSB9XG4gICAgICAgICAgfVxuICAgICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgaWYgKGNvbW1hbmQgPT0gJ2J1aWxkJylcbiAgICAgICAgICAgICAge3Bhcm1zID0gWydhcHAnLCBjb21tYW5kLCBvcHRpb25zLnByb2ZpbGUsIG9wdGlvbnMuZW52aXJvbm1lbnRdfVxuICAgICAgICAgICAgZWxzZVxuICAgICAgICAgICAgICB7cGFybXMgPSBbJ2FwcCcsIGNvbW1hbmQsICctLXdlYi1zZXJ2ZXInLCAnZmFsc2UnLCBvcHRpb25zLnByb2ZpbGUsIG9wdGlvbnMuZW52aXJvbm1lbnRdfVxuICAgICAgICAgIH1cbiAgICAgICAgICBpZiAodmFycy53YXRjaFN0YXJ0ZWQgPT0gZmFsc2UpIHtcbiAgICAgICAgICAgIGF3YWl0IF9idWlsZEV4dEJ1bmRsZShhcHAsIGNvbXBpbGF0aW9uLCBvdXRwdXRQYXRoLCBwYXJtcywgdmFycywgb3B0aW9ucylcbiAgICAgICAgICAgIHZhcnMud2F0Y2hTdGFydGVkID0gdHJ1ZVxuICAgICAgICAgIH1cbiAgICAgICAgICBjYWxsYmFjaygpXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgY2FsbGJhY2soKVxuICAgICAgICB9XG4gICAgICB9XG4gICAgICBlbHNlIHtcbiAgICAgICAgbG9ndih2ZXJib3NlLCdOT1QgcnVubmluZyBlbWl0JylcbiAgICAgICAgY2FsbGJhY2soKVxuICAgICAgfVxuICAgIH1cbiAgICBlbHNlIHtcbiAgICAgIGxvZ3YodmVyYm9zZSwnZW1pdCBpcyBubycpXG4gICAgICBjYWxsYmFjaygpXG4gICAgfVxuICB9XG4gIGNhdGNoKGUpIHtcbiAgICBjYWxsYmFjaygpXG4gICAgdGhyb3cgJ19lbWl0OiAnICsgZS50b1N0cmluZygpXG4gICAgLy8gbG9ndihvcHRpb25zLnZlcmJvc2UsZSlcbiAgICAvLyBjb21waWxhdGlvbi5lcnJvcnMucHVzaCgnX2VtaXQ6ICcgKyBlKVxuICAgIC8vIGNhbGxiYWNrKClcbiAgfVxufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBfZG9uZShzdGF0cywgdmFycywgb3B0aW9ucykge1xuICB0cnkge1xuICAgIHZhciB2ZXJib3NlID0gb3B0aW9ucy52ZXJib3NlXG4gICAgdmFyIGZyYW1ld29yayA9IG9wdGlvbnMuZnJhbWV3b3JrXG4gICAgbG9ndih2ZXJib3NlLCdGVU5DVElPTiBfZG9uZScpXG4gICAgaWYgKHN0YXRzLmNvbXBpbGF0aW9uLmVycm9ycyAmJiBzdGF0cy5jb21waWxhdGlvbi5lcnJvcnMubGVuZ3RoKSAvLyAmJiBwcm9jZXNzLmFyZ3YuaW5kZXhPZignLS13YXRjaCcpID09IC0xKVxuICAgIHtcbiAgICAgIHZhciBjaGFsayA9IHJlcXVpcmUoJ2NoYWxrJyk7XG4gICAgICBjb25zb2xlLmxvZyhjaGFsay5yZWQoJyoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKicpKTtcbiAgICAgIGNvbnNvbGUubG9nKHN0YXRzLmNvbXBpbGF0aW9uLmVycm9yc1swXSk7XG4gICAgICBjb25zb2xlLmxvZyhjaGFsay5yZWQoJyoqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKioqKicpKTtcbiAgICAgIHByb2Nlc3MuZXhpdCgwKTtcbiAgICB9XG5cbiAgICAvL21qZyByZWZhY3RvclxuICAgIGlmICh2YXJzLnByb2R1Y3Rpb24gPT0gdHJ1ZSAmJiBvcHRpb25zLnRyZWVzaGFrZSA9PSAnbm8nICYmIGZyYW1ld29yayA9PSAnYW5ndWxhcicpIHtcbiAgICAgIHJlcXVpcmUoYC4vJHtvcHRpb25zLmZyYW1ld29ya31VdGlsYCkuX3RvRGV2KHZhcnMsIG9wdGlvbnMpXG4gICAgfVxuICAgIHRyeSB7XG4gICAgICBpZihvcHRpb25zLmJyb3dzZXIgPT0gJ3llcycgJiYgb3B0aW9ucy53YXRjaCA9PSAneWVzJyAmJiB2YXJzLnByb2R1Y3Rpb24gPT0gZmFsc2UpIHtcbiAgICAgICAgaWYgKHZhcnMuYnJvd3NlckNvdW50ID09IDApIHtcbiAgICAgICAgICB2YXIgdXJsID0gJ2h0dHA6Ly9sb2NhbGhvc3Q6JyArIG9wdGlvbnMucG9ydFxuICAgICAgICAgIHJlcXVpcmUoJy4vcGx1Z2luVXRpbCcpLmxvZyh2YXJzLmFwcCwgYE9wZW5pbmcgYnJvd3NlciBhdCAke3VybH1gKVxuICAgICAgICAgIHZhcnMuYnJvd3NlckNvdW50KytcbiAgICAgICAgICBjb25zdCBvcG4gPSByZXF1aXJlKCdvcG4nKVxuICAgICAgICAgIG9wbih1cmwpXG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gICAgY2F0Y2ggKGUpIHtcbiAgICAgIGNvbnNvbGUubG9nKGUpXG4gICAgfVxuICAgIGlmICh2YXJzLmJ1aWxkc3RlcCA9PSAnMSBvZiAxJykge1xuICAgICAgaWYgKHZhcnMucHJvZHVjdGlvbiA9PSB0cnVlKSB7XG4gICAgICAgIHJlcXVpcmUoJy4vcGx1Z2luVXRpbCcpLmxvZyh2YXJzLmFwcCwgYEVuZGluZyBwcm9kdWN0aW9uIGJ1aWxkIGZvciAke2ZyYW1ld29ya31gKVxuICAgICAgfVxuICAgICAgZWxzZSB7XG4gICAgICAgIHJlcXVpcmUoJy4vcGx1Z2luVXRpbCcpLmxvZyh2YXJzLmFwcCwgYEVuZGluZyBkZXZlbG9wbWVudCBidWlsZCBmb3IgJHtmcmFtZXdvcmt9YClcbiAgICAgIH1cbiAgICB9XG4gICAgaWYgKHZhcnMuYnVpbGRzdGVwID09ICcyIG9mIDInKSB7XG4gICAgICByZXF1aXJlKCcuL3BsdWdpblV0aWwnKS5sb2codmFycy5hcHAsIGBFbmRpbmcgcHJvZHVjdGlvbiBidWlsZCBmb3IgJHtmcmFtZXdvcmt9YClcbiAgICB9XG4gIH1cbiAgY2F0Y2goZSkge1xuLy8gICAgcmVxdWlyZSgnLi9wbHVnaW5VdGlsJykubG9ndihvcHRpb25zLnZlcmJvc2UsZSlcbiAgICB0aHJvdyAnX2RvbmU6ICcgKyBlLnRvU3RyaW5nKClcbiAgfVxufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBfcHJlcGFyZUZvckJ1aWxkKGFwcCwgdmFycywgb3B0aW9ucywgb3V0cHV0LCBjb21waWxhdGlvbikge1xuICB0cnkge1xuICAgIHZhciB2ZXJib3NlID0gb3B0aW9ucy52ZXJib3NlXG4gICAgdmFyIHBhY2thZ2VzID0gb3B0aW9ucy5wYWNrYWdlc1xuICAgIHZhciB0b29sa2l0ID0gb3B0aW9ucy50b29sa2l0XG4gICAgdmFyIHRoZW1lID0gb3B0aW9ucy50aGVtZVxuICAgIGxvZ3YodmVyYm9zZSwnRlVOQ1RJT04gX3ByZXBhcmVGb3JCdWlsZCcpXG4gICAgY29uc3QgcmltcmFmID0gcmVxdWlyZSgncmltcmFmJylcbiAgICBjb25zdCBta2RpcnAgPSByZXF1aXJlKCdta2RpcnAnKVxuICAgIGNvbnN0IGZzeCA9IHJlcXVpcmUoJ2ZzLWV4dHJhJylcbiAgICBjb25zdCBmcyA9IHJlcXVpcmUoJ2ZzJylcbiAgICBjb25zdCBwYXRoID0gcmVxdWlyZSgncGF0aCcpXG4gICAgdGhlbWUgPSB0aGVtZSB8fCAodG9vbGtpdCA9PT0gJ2NsYXNzaWMnID8gJ3RoZW1lLXRyaXRvbicgOiAndGhlbWUtbWF0ZXJpYWwnKVxuICAgIGxvZ3YodmVyYm9zZSwnZmlyc3RUaW1lOiAnICsgdmFycy5maXJzdFRpbWUpXG4gICAgaWYgKHZhcnMuZmlyc3RUaW1lKSB7XG4gICAgICByaW1yYWYuc3luYyhvdXRwdXQpXG4gICAgICBta2RpcnAuc3luYyhvdXRwdXQpXG4gICAgICBjb25zdCBidWlsZFhNTCA9IHJlcXVpcmUoJy4vYXJ0aWZhY3RzJykuYnVpbGRYTUxcbiAgICAgIGNvbnN0IGNyZWF0ZUFwcEpzb24gPSByZXF1aXJlKCcuL2FydGlmYWN0cycpLmNyZWF0ZUFwcEpzb25cbiAgICAgIGNvbnN0IGNyZWF0ZVdvcmtzcGFjZUpzb24gPSByZXF1aXJlKCcuL2FydGlmYWN0cycpLmNyZWF0ZVdvcmtzcGFjZUpzb25cbiAgICAgIGNvbnN0IGNyZWF0ZUpTRE9NRW52aXJvbm1lbnQgPSByZXF1aXJlKCcuL2FydGlmYWN0cycpLmNyZWF0ZUpTRE9NRW52aXJvbm1lbnRcbiAgICAgIGZzLndyaXRlRmlsZVN5bmMocGF0aC5qb2luKG91dHB1dCwgJ2J1aWxkLnhtbCcpLCBidWlsZFhNTCh2YXJzLnByb2R1Y3Rpb24sIG9wdGlvbnMsIG91dHB1dCksICd1dGY4JylcbiAgICAgIGZzLndyaXRlRmlsZVN5bmMocGF0aC5qb2luKG91dHB1dCwgJ2FwcC5qc29uJyksIGNyZWF0ZUFwcEpzb24odGhlbWUsIHBhY2thZ2VzLCB0b29sa2l0LCBvcHRpb25zLCBvdXRwdXQpLCAndXRmOCcpXG4gICAgICBmcy53cml0ZUZpbGVTeW5jKHBhdGguam9pbihvdXRwdXQsICdqc2RvbS1lbnZpcm9ubWVudC5qcycpLCBjcmVhdGVKU0RPTUVudmlyb25tZW50KG9wdGlvbnMsIG91dHB1dCksICd1dGY4JylcbiAgICAgIGZzLndyaXRlRmlsZVN5bmMocGF0aC5qb2luKG91dHB1dCwgJ3dvcmtzcGFjZS5qc29uJyksIGNyZWF0ZVdvcmtzcGFjZUpzb24ob3B0aW9ucywgb3V0cHV0KSwgJ3V0ZjgnKVxuICAgICAgdmFyIGZyYW1ld29yayA9IHZhcnMuZnJhbWV3b3JrO1xuICAgICAgLy9iZWNhdXNlIG9mIGEgcHJvYmxlbSB3aXRoIGNvbG9ycGlja2VyXG4gICAgICBpZiAoZnMuZXhpc3RzU3luYyhwYXRoLmpvaW4ocHJvY2Vzcy5jd2QoKSxgZXh0LSR7ZnJhbWV3b3JrfS91eC9gKSkpIHtcbiAgICAgICAgdmFyIGZyb21QYXRoID0gcGF0aC5qb2luKHByb2Nlc3MuY3dkKCksIGBleHQtJHtmcmFtZXdvcmt9L3V4L2ApXG4gICAgICAgIHZhciB0b1BhdGggPSBwYXRoLmpvaW4ob3V0cHV0LCAndXgnKVxuICAgICAgICBmc3guY29weVN5bmMoZnJvbVBhdGgsIHRvUGF0aClcbiAgICAgICAgbG9nKGFwcCwgJ0NvcHlpbmcgKHV4KSAnICsgZnJvbVBhdGgucmVwbGFjZShwcm9jZXNzLmN3ZCgpLCAnJykgKyAnIHRvOiAnICsgdG9QYXRoLnJlcGxhY2UocHJvY2Vzcy5jd2QoKSwgJycpKVxuICAgICAgfVxuICAgICAgaWYgKGZzLmV4aXN0c1N5bmMocGF0aC5qb2luKHByb2Nlc3MuY3dkKCksYGV4dC0ke2ZyYW1ld29ya30vcGFja2FnZXMvYCkpKSB7XG4gICAgICAgIHZhciBmcm9tUGF0aCA9IHBhdGguam9pbihwcm9jZXNzLmN3ZCgpLCBgZXh0LSR7ZnJhbWV3b3JrfS9wYWNrYWdlcy9gKVxuICAgICAgICB2YXIgdG9QYXRoID0gcGF0aC5qb2luKG91dHB1dCwgJ3BhY2thZ2VzJylcbiAgICAgICAgZnN4LmNvcHlTeW5jKGZyb21QYXRoLCB0b1BhdGgpXG4gICAgICAgIGxvZyhhcHAsICdDb3B5aW5nICcgKyBmcm9tUGF0aC5yZXBsYWNlKHByb2Nlc3MuY3dkKCksICcnKSArICcgdG86ICcgKyB0b1BhdGgucmVwbGFjZShwcm9jZXNzLmN3ZCgpLCAnJykpXG4gICAgICB9XG4gICAgICBpZiAoZnMuZXhpc3RzU3luYyhwYXRoLmpvaW4ocHJvY2Vzcy5jd2QoKSxgZXh0LSR7ZnJhbWV3b3JrfS9vdmVycmlkZXMvYCkpKSB7XG4gICAgICAgIHZhciBmcm9tUGF0aCA9IHBhdGguam9pbihwcm9jZXNzLmN3ZCgpLCBgZXh0LSR7ZnJhbWV3b3JrfS9vdmVycmlkZXMvYClcbiAgICAgICAgdmFyIHRvUGF0aCA9IHBhdGguam9pbihvdXRwdXQsICdvdmVycmlkZXMnKVxuICAgICAgICBmc3guY29weVN5bmMoZnJvbVBhdGgsIHRvUGF0aClcbiAgICAgICAgbG9nKGFwcCwgJ0NvcHlpbmcgJyArIGZyb21QYXRoLnJlcGxhY2UocHJvY2Vzcy5jd2QoKSwgJycpICsgJyB0bzogJyArIHRvUGF0aC5yZXBsYWNlKHByb2Nlc3MuY3dkKCksICcnKSlcbiAgICAgIH1cbiAgICAgIGlmIChmcy5leGlzdHNTeW5jKHBhdGguam9pbihwcm9jZXNzLmN3ZCgpLCdyZXNvdXJjZXMvJykpKSB7XG4gICAgICAgIHZhciBmcm9tUmVzb3VyY2VzID0gcGF0aC5qb2luKHByb2Nlc3MuY3dkKCksICdyZXNvdXJjZXMvJylcbiAgICAgICAgdmFyIHRvUmVzb3VyY2VzID0gcGF0aC5qb2luKG91dHB1dCwgJy4uL3Jlc291cmNlcycpXG4gICAgICAgIGZzeC5jb3B5U3luYyhmcm9tUmVzb3VyY2VzLCB0b1Jlc291cmNlcylcbiAgICAgICAgbG9nKGFwcCwgJ0NvcHlpbmcgJyArIGZyb21SZXNvdXJjZXMucmVwbGFjZShwcm9jZXNzLmN3ZCgpLCAnJykgKyAnIHRvOiAnICsgdG9SZXNvdXJjZXMucmVwbGFjZShwcm9jZXNzLmN3ZCgpLCAnJykpXG4gICAgICB9XG4gICAgfVxuICAgIHZhcnMuZmlyc3RUaW1lID0gZmFsc2VcbiAgICB2YXIganMgPSAnJ1xuICAgIGlmICh2YXJzLnByb2R1Y3Rpb24pIHtcbiAgICAgIHZhcnMuZGVwcyA9IHZhcnMuZGVwcy5maWx0ZXIoZnVuY3Rpb24odmFsdWUsIGluZGV4KXsgcmV0dXJuIHZhcnMuZGVwcy5pbmRleE9mKHZhbHVlKSA9PSBpbmRleCB9KTtcbiAgICAgIGpzID0gdmFycy5kZXBzLmpvaW4oJztcXG4nKTtcbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICBqcyA9IGBFeHQucmVxdWlyZShbXCJFeHQuKlwiLFwiRXh0LmRhdGEuVHJlZVN0b3JlXCJdKWBcbiAgICB9XG4gICAgaWYgKHZhcnMubWFuaWZlc3QgPT09IG51bGwgfHwganMgIT09IHZhcnMubWFuaWZlc3QpIHtcbiAgICAgIHZhcnMubWFuaWZlc3QgPSBqcyArICc7XFxuRXh0LnJlcXVpcmUoW1wiRXh0LmxheW91dC4qXCJdKTtcXG4nO1xuICAgICAgY29uc3QgbWFuaWZlc3QgPSBwYXRoLmpvaW4ob3V0cHV0LCAnbWFuaWZlc3QuanMnKVxuICAgICAgZnMud3JpdGVGaWxlU3luYyhtYW5pZmVzdCwgdmFycy5tYW5pZmVzdCwgJ3V0ZjgnKVxuICAgICAgdmFycy5yZWJ1aWxkID0gdHJ1ZVxuICAgICAgdmFyIGJ1bmRsZURpciA9IG91dHB1dC5yZXBsYWNlKHByb2Nlc3MuY3dkKCksICcnKVxuICAgICAgaWYgKGJ1bmRsZURpci50cmltKCkgPT0gJycpIHtidW5kbGVEaXIgPSAnLi8nfVxuICAgICAgbG9nKGFwcCwgJ0J1aWxkaW5nIEV4dCBidW5kbGUgYXQ6ICcgKyBidW5kbGVEaXIpXG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgdmFycy5yZWJ1aWxkID0gZmFsc2VcbiAgICAgIGxvZyhhcHAsICdFeHQgcmVidWlsZCBOT1QgbmVlZGVkJylcbiAgICB9XG4gIH1cbiAgY2F0Y2goZSkge1xuICAgIHJlcXVpcmUoJy4vcGx1Z2luVXRpbCcpLmxvZ3Yob3B0aW9ucy52ZXJib3NlLGUpXG4gICAgY29tcGlsYXRpb24uZXJyb3JzLnB1c2goJ19wcmVwYXJlRm9yQnVpbGQ6ICcgKyBlKVxuICB9XG59XG5cbi8vKioqKioqKioqKlxuZXhwb3J0IGZ1bmN0aW9uIF9idWlsZEV4dEJ1bmRsZShhcHAsIGNvbXBpbGF0aW9uLCBvdXRwdXRQYXRoLCBwYXJtcywgdmFycywgb3B0aW9ucykge1xuLy8gIHRyeSB7XG4gICAgdmFyIHZlcmJvc2UgPSBvcHRpb25zLnZlcmJvc2VcbiAgICBjb25zdCBmcyA9IHJlcXVpcmUoJ2ZzJylcbiAgICBsb2d2KHZlcmJvc2UsJ0ZVTkNUSU9OIF9idWlsZEV4dEJ1bmRsZScpXG4gICAgbGV0IHNlbmNoYTsgdHJ5IHsgc2VuY2hhID0gcmVxdWlyZSgnQHNlbmNoYS9jbWQnKSB9IGNhdGNoIChlKSB7IHNlbmNoYSA9ICdzZW5jaGEnIH1cbiAgICBpZiAoZnMuZXhpc3RzU3luYyhzZW5jaGEpKSB7XG4gICAgICBsb2d2KHZlcmJvc2UsJ3NlbmNoYSBmb2xkZXIgZXhpc3RzJylcbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICBsb2d2KHZlcmJvc2UsJ3NlbmNoYSBmb2xkZXIgRE9FUyBOT1QgZXhpc3QnKVxuICAgIH1cbiAgICByZXR1cm4gbmV3IFByb21pc2UoKHJlc29sdmUsIHJlamVjdCkgPT4ge1xuICAgICAgY29uc3Qgb25CdWlsZERvbmUgPSAoKSA9PiB7XG4gICAgICAgIGxvZ3YodmVyYm9zZSwnb25CdWlsZERvbmUnKVxuICAgICAgICByZXNvbHZlKClcbiAgICAgIH1cbiAgICAgIHZhciBvcHRzID0geyBjd2Q6IG91dHB1dFBhdGgsIHNpbGVudDogdHJ1ZSwgc3RkaW86ICdwaXBlJywgZW5jb2Rpbmc6ICd1dGYtOCd9XG4gICAgICBfZXhlY3V0ZUFzeW5jKGFwcCwgc2VuY2hhLCBwYXJtcywgb3B0cywgY29tcGlsYXRpb24sIHZhcnMsIG9wdGlvbnMpLnRoZW4gKFxuICAgICAgICBmdW5jdGlvbigpIHsgb25CdWlsZERvbmUoKSB9LFxuICAgICAgICBmdW5jdGlvbihyZWFzb24pIHsgcmVqZWN0KHJlYXNvbikgfVxuICAgICAgKVxuICAgIH0pXG4gIC8vIH1cbiAgLy8gY2F0Y2goZSkge1xuICAvLyAgIGNvbnNvbGUubG9nKCdlJylcbiAgLy8gICByZXF1aXJlKCcuL3BsdWdpblV0aWwnKS5sb2d2KG9wdGlvbnMudmVyYm9zZSxlKVxuICAvLyAgIGNvbXBpbGF0aW9uLmVycm9ycy5wdXNoKCdfYnVpbGRFeHRCdW5kbGU6ICcgKyBlKVxuICAvLyAgIGNhbGxiYWNrKClcbiAgLy8gfVxufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBhc3luYyBmdW5jdGlvbiBfZXhlY3V0ZUFzeW5jIChhcHAsIGNvbW1hbmQsIHBhcm1zLCBvcHRzLCBjb21waWxhdGlvbiwgdmFycywgb3B0aW9ucykge1xuLy8gIHRyeSB7XG4gICAgdmFyIHZlcmJvc2UgPSBvcHRpb25zLnZlcmJvc2VcbiAgICB2YXIgZnJhbWV3b3JrID0gb3B0aW9ucy5mcmFtZXdvcmtcbiAgICAvL2NvbnN0IERFRkFVTFRfU1VCU1RSUyA9IFsnW0lORl0gTG9hZGluZycsICdbSU5GXSBQcm9jZXNzaW5nJywgJ1tMT0ddIEZhc2hpb24gYnVpbGQgY29tcGxldGUnLCAnW0VSUl0nLCAnW1dSTl0nLCBcIltJTkZdIFNlcnZlclwiLCBcIltJTkZdIFdyaXRpbmdcIiwgXCJbSU5GXSBMb2FkaW5nIEJ1aWxkXCIsIFwiW0lORl0gV2FpdGluZ1wiLCBcIltMT0ddIEZhc2hpb24gd2FpdGluZ1wiXTtcbiAgICBjb25zdCBERUZBVUxUX1NVQlNUUlMgPSBbXCJbSU5GXSB4U2VydmVyXCIsICdbSU5GXSBMb2FkaW5nJywgJ1tJTkZdIEFwcGVuZCcsICdbSU5GXSBQcm9jZXNzaW5nJywgJ1tJTkZdIFByb2Nlc3NpbmcgQnVpbGQnLCAnW0xPR10gRmFzaGlvbiBidWlsZCBjb21wbGV0ZScsICdbRVJSXScsICdbV1JOXScsIFwiW0lORl0gV3JpdGluZ1wiLCBcIltJTkZdIExvYWRpbmcgQnVpbGRcIiwgXCJbSU5GXSBXYWl0aW5nXCIsIFwiW0xPR10gRmFzaGlvbiB3YWl0aW5nXCJdO1xuICAgIHZhciBzdWJzdHJpbmdzID0gREVGQVVMVF9TVUJTVFJTXG4gICAgdmFyIGNoYWxrID0gcmVxdWlyZSgnY2hhbGsnKVxuICAgIGNvbnN0IGNyb3NzU3Bhd24gPSByZXF1aXJlKCdjcm9zcy1zcGF3bicpXG4gICAgbG9ndih2ZXJib3NlLCAnRlVOQ1RJT04gX2V4ZWN1dGVBc3luYycpXG4gICAgYXdhaXQgbmV3IFByb21pc2UoKHJlc29sdmUsIHJlamVjdCkgPT4ge1xuICAgICAgbG9ndih2ZXJib3NlLGBjb21tYW5kIC0gJHtjb21tYW5kfWApXG4gICAgICBsb2d2KHZlcmJvc2UsIGBwYXJtcyAtICR7cGFybXN9YClcbiAgICAgIGxvZ3YodmVyYm9zZSwgYG9wdHMgLSAke0pTT04uc3RyaW5naWZ5KG9wdHMpfWApXG4gICAgICBsZXQgY2hpbGQgPSBjcm9zc1NwYXduKGNvbW1hbmQsIHBhcm1zLCBvcHRzKVxuICAgICAgY2hpbGQub24oJ2Nsb3NlJywgKGNvZGUsIHNpZ25hbCkgPT4ge1xuICAgICAgICBsb2d2KHZlcmJvc2UsIGBvbiBjbG9zZTogYCArIGNvZGUpXG4gICAgICAgIGlmKGNvZGUgPT09IDApIHsgcmVzb2x2ZSgwKSB9XG4gICAgICAgIGVsc2UgeyBjb21waWxhdGlvbi5lcnJvcnMucHVzaCggbmV3IEVycm9yKGNvZGUpICk7IHJlc29sdmUoMCkgfVxuICAgICAgfSlcbiAgICAgIGNoaWxkLm9uKCdlcnJvcicsIChlcnJvcikgPT4ge1xuICAgICAgICBsb2d2KHZlcmJvc2UsIGBvbiBlcnJvcmApXG4gICAgICAgIGNvbXBpbGF0aW9uLmVycm9ycy5wdXNoKGVycm9yKVxuICAgICAgICByZXNvbHZlKDApXG4gICAgICB9KVxuICAgICAgY2hpbGQuc3Rkb3V0Lm9uKCdkYXRhJywgKGRhdGEpID0+IHtcbiAgICAgICAgdmFyIHN0ciA9IGRhdGEudG9TdHJpbmcoKS5yZXBsYWNlKC9cXHI/XFxufFxcci9nLCBcIiBcIikudHJpbSgpXG4gICAgICAgIGxvZ3YodmVyYm9zZSwgYCR7c3RyfWApXG4gICAgICAgIGlmIChkYXRhICYmIGRhdGEudG9TdHJpbmcoKS5tYXRjaCgvRmFzaGlvbiB3YWl0aW5nIGZvciBjaGFuZ2VzXFwuXFwuXFwuLykpIHtcblxuICAgICAgICAgIGNvbnN0IGZzID0gcmVxdWlyZSgnZnMnKTtcbiAgICAgICAgICB2YXIgZmlsZW5hbWUgPSBwcm9jZXNzLmN3ZCgpICsgdmFycy50b3VjaEZpbGU7XG4gICAgICAgICAgdHJ5IHtcbiAgICAgICAgICAgIHZhciBkID0gbmV3IERhdGUoKS50b0xvY2FsZVN0cmluZygpXG4gICAgICAgICAgICB2YXIgZGF0YSA9IGZzLnJlYWRGaWxlU3luYyhmaWxlbmFtZSk7XG4gICAgICAgICAgICAvLyBmcy53cml0ZUZpbGVTeW5jKGZpbGVuYW1lLCAnLy8nICsgZCwgJ3V0ZjgnKTtcbiAgICAgICAgICAgIGxvZ3YoYXBwLCBgdG91Y2hpbmcgJHtmaWxlbmFtZX1gKTtcbiAgICAgICAgICB9XG4gICAgICAgICAgY2F0Y2goZSkge1xuICAgICAgICAgICAgbG9ndihhcHAsIGBOT1QgdG91Y2hpbmcgJHtmaWxlbmFtZX1gKTtcbiAgICAgICAgICB9XG5cbiAgICAgICAgICByZXNvbHZlKDApXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgaWYgKHN1YnN0cmluZ3Muc29tZShmdW5jdGlvbih2KSB7IHJldHVybiBkYXRhLmluZGV4T2YodikgPj0gMDsgfSkpIHtcbiAgICAgICAgICAgIHN0ciA9IHN0ci5yZXBsYWNlKFwiW0lORl1cIiwgXCJcIilcbiAgICAgICAgICAgIHN0ciA9IHN0ci5yZXBsYWNlKFwiW0xPR11cIiwgXCJcIilcbiAgICAgICAgICAgIHN0ciA9IHN0ci5yZXBsYWNlKHByb2Nlc3MuY3dkKCksICcnKS50cmltKClcbiAgICAgICAgICAgIGlmIChzdHIuaW5jbHVkZXMoXCJbRVJSXVwiKSkge1xuICAgICAgICAgICAgICBjb21waWxhdGlvbi5lcnJvcnMucHVzaChhcHAgKyBzdHIucmVwbGFjZSgvXlxcW0VSUlxcXSAvZ2ksICcnKSk7XG4gICAgICAgICAgICAgIHN0ciA9IHN0ci5yZXBsYWNlKFwiW0VSUl1cIiwgYCR7Y2hhbGsucmVkKFwiW0VSUl1cIil9YClcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGxvZyhhcHAsIHN0cilcbiAgICAgICAgICB9XG4gICAgICAgIH1cbiAgICAgIH0pXG4gICAgICBjaGlsZC5zdGRlcnIub24oJ2RhdGEnLCAoZGF0YSkgPT4ge1xuICAgICAgICBsb2d2KG9wdGlvbnMsIGBlcnJvciBvbiBjbG9zZTogYCArIGRhdGEpXG4gICAgICAgIHZhciBzdHIgPSBkYXRhLnRvU3RyaW5nKCkucmVwbGFjZSgvXFxyP1xcbnxcXHIvZywgXCIgXCIpLnRyaW0oKVxuICAgICAgICB2YXIgc3RySmF2YU9wdHMgPSBcIlBpY2tlZCB1cCBfSkFWQV9PUFRJT05TXCI7XG4gICAgICAgIHZhciBpbmNsdWRlcyA9IHN0ci5pbmNsdWRlcyhzdHJKYXZhT3B0cylcbiAgICAgICAgaWYgKCFpbmNsdWRlcykge1xuICAgICAgICAgIGNvbnNvbGUubG9nKGAke2FwcH0gJHtjaGFsay5yZWQoXCJbRVJSXVwiKX0gJHtzdHJ9YClcbiAgICAgICAgfVxuICAgICAgfSlcbiAgICB9KVxuICAvLyB9XG4gIC8vIGNhdGNoKGUpIHtcbiAgLy8gICBsb2d2KG9wdGlvbnMsZSlcbiAgLy8gICBjb21waWxhdGlvbi5lcnJvcnMucHVzaCgnX2V4ZWN1dGVBc3luYzogJyArIGUpXG4gIC8vICAgY2FsbGJhY2soKVxuICAvLyB9XG59XG5cbi8vKioqKioqKioqKlxuZnVuY3Rpb24gcnVuU2NyaXB0KHNjcmlwdFBhdGgsIGNhbGxiYWNrKSB7XG4gIHZhciBjaGlsZFByb2Nlc3MgPSByZXF1aXJlKCdjaGlsZF9wcm9jZXNzJyk7XG4gIC8vIGtlZXAgdHJhY2sgb2Ygd2hldGhlciBjYWxsYmFjayBoYXMgYmVlbiBpbnZva2VkIHRvIHByZXZlbnQgbXVsdGlwbGUgaW52b2NhdGlvbnNcbiAgdmFyIGludm9rZWQgPSBmYWxzZTtcbiAgdmFyIHByb2Nlc3MgPSBjaGlsZFByb2Nlc3MuZm9yayhzY3JpcHRQYXRoKTtcbiAgLy8gbGlzdGVuIGZvciBlcnJvcnMgYXMgdGhleSBtYXkgcHJldmVudCB0aGUgZXhpdCBldmVudCBmcm9tIGZpcmluZ1xuICBwcm9jZXNzLm9uKCdlcnJvcicsIGZ1bmN0aW9uIChlcnIpIHtcbiAgICBpZiAoaW52b2tlZCkgcmV0dXJuO1xuICAgIGludm9rZWQgPSB0cnVlO1xuICAgIGNhbGxiYWNrKGVycik7XG4gIH0pO1xuICAvLyBleGVjdXRlIHRoZSBjYWxsYmFjayBvbmNlIHRoZSBwcm9jZXNzIGhhcyBmaW5pc2hlZCBydW5uaW5nXG4gIHByb2Nlc3Mub24oJ2V4aXQnLCBmdW5jdGlvbiAoY29kZSkge1xuICAgIGlmIChpbnZva2VkKSByZXR1cm47XG4gICAgaW52b2tlZCA9IHRydWU7XG4gICAgdmFyIGVyciA9IGNvZGUgPT09IDAgPyBudWxsIDogbmV3IEVycm9yKCdleGl0IGNvZGUgJyArIGNvZGUpO1xuICAgIGNhbGxiYWNrKGVycik7XG4gIH0pO1xufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBfdG9YdHlwZShzdHIpIHtcbiAgcmV0dXJuIHN0ci50b0xvd2VyQ2FzZSgpLnJlcGxhY2UoL18vZywgJy0nKVxufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBfZ2V0QXBwKCkge1xuICB2YXIgY2hhbGsgPSByZXF1aXJlKCdjaGFsaycpXG4gIHZhciBwcmVmaXggPSBgYFxuICBjb25zdCBwbGF0Zm9ybSA9IHJlcXVpcmUoJ29zJykucGxhdGZvcm0oKVxuICBpZiAocGxhdGZvcm0gPT0gJ2RhcndpbicpIHsgcHJlZml4ID0gYOKEuSDvvaJleHTvvaM6YCB9XG4gIGVsc2UgeyBwcmVmaXggPSBgaSBbZXh0XTpgIH1cbiAgcmV0dXJuIGAke2NoYWxrLmdyZWVuKHByZWZpeCl9IGBcbn1cblxuLy8qKioqKioqKioqXG5leHBvcnQgZnVuY3Rpb24gX2dldFZlcnNpb25zKHBsdWdpbk5hbWUsIGZyYW1ld29ya05hbWUpIHtcbnRyeSB7XG4gIGNvbnN0IHBhdGggPSByZXF1aXJlKCdwYXRoJylcbiAgY29uc3QgZnMgPSByZXF1aXJlKCdmcycpXG4gIHZhciB2ID0ge31cbiAgdmFyIGZyYW1ld29ya0luZm8gPSAnbi9hJ1xuXG4gIHYucGx1Z2luVmVyc2lvbiA9ICduL2EnO1xuICB2LmV4dFZlcnNpb24gPSAnbi9hJztcbiAgdi5lZGl0aW9uID0gJ24vYSc7XG4gIHYuY21kVmVyc2lvbiA9ICduL2EnO1xuICB2LndlYnBhY2tWZXJzaW9uID0gJ24vYSc7XG5cbiAgdmFyIHBsdWdpblBhdGggPSBwYXRoLnJlc29sdmUocHJvY2Vzcy5jd2QoKSwnbm9kZV9tb2R1bGVzL0BzZW5jaGEnLCBwbHVnaW5OYW1lKVxuICB2YXIgcGx1Z2luUGtnID0gKGZzLmV4aXN0c1N5bmMocGx1Z2luUGF0aCsnL3BhY2thZ2UuanNvbicpICYmIEpTT04ucGFyc2UoZnMucmVhZEZpbGVTeW5jKHBsdWdpblBhdGgrJy9wYWNrYWdlLmpzb24nLCAndXRmLTgnKSkgfHwge30pO1xuICB2LnBsdWdpblZlcnNpb24gPSBwbHVnaW5Qa2cudmVyc2lvblxuICB2Ll9yZXNvbHZlZCA9IHBsdWdpblBrZy5fcmVzb2x2ZWRcbiAgaWYgKHYuX3Jlc29sdmVkID09IHVuZGVmaW5lZCkge1xuICAgIHYuZWRpdGlvbiA9IGBDb21tZXJjaWFsYFxuICB9XG4gIGVsc2Uge1xuICAgIGlmICgtMSA9PSB2Ll9yZXNvbHZlZC5pbmRleE9mKCdjb21tdW5pdHknKSkge1xuICAgICAgdi5lZGl0aW9uID0gYENvbW1lcmNpYWxgXG4gICAgfVxuICAgIGVsc2Uge1xuICAgICAgdi5lZGl0aW9uID0gYENvbW11bml0eWBcbiAgICB9XG4gIH1cbiAgdmFyIHdlYnBhY2tQYXRoID0gcGF0aC5yZXNvbHZlKHByb2Nlc3MuY3dkKCksJ25vZGVfbW9kdWxlcy93ZWJwYWNrJylcbiAgdmFyIHdlYnBhY2tQa2cgPSAoZnMuZXhpc3RzU3luYyh3ZWJwYWNrUGF0aCsnL3BhY2thZ2UuanNvbicpICYmIEpTT04ucGFyc2UoZnMucmVhZEZpbGVTeW5jKHdlYnBhY2tQYXRoKycvcGFja2FnZS5qc29uJywgJ3V0Zi04JykpIHx8IHt9KTtcbiAgdi53ZWJwYWNrVmVyc2lvbiA9IHdlYnBhY2tQa2cudmVyc2lvblxuICB2YXIgZXh0UGF0aCA9IHBhdGgucmVzb2x2ZShwcm9jZXNzLmN3ZCgpLCdub2RlX21vZHVsZXMvQHNlbmNoYS9leHQnKVxuICB2YXIgZXh0UGtnID0gKGZzLmV4aXN0c1N5bmMoZXh0UGF0aCsnL3BhY2thZ2UuanNvbicpICYmIEpTT04ucGFyc2UoZnMucmVhZEZpbGVTeW5jKGV4dFBhdGgrJy9wYWNrYWdlLmpzb24nLCAndXRmLTgnKSkgfHwge30pO1xuICB2LmV4dFZlcnNpb24gPSBleHRQa2cuc2VuY2hhLnZlcnNpb25cbiAgdmFyIGNtZFBhdGggPSBwYXRoLnJlc29sdmUocHJvY2Vzcy5jd2QoKSxgbm9kZV9tb2R1bGVzL0BzZW5jaGEvY21kYClcbiAgdmFyIGNtZFBrZyA9IChmcy5leGlzdHNTeW5jKGNtZFBhdGgrJy9wYWNrYWdlLmpzb24nKSAmJiBKU09OLnBhcnNlKGZzLnJlYWRGaWxlU3luYyhjbWRQYXRoKycvcGFja2FnZS5qc29uJywgJ3V0Zi04JykpIHx8IHt9KTtcbiAgdi5jbWRWZXJzaW9uID0gY21kUGtnLnZlcnNpb25fZnVsbFxuICBpZiAodi5jbWRWZXJzaW9uID09IHVuZGVmaW5lZCkge1xuICAgIHZhciBjbWRQYXRoID0gcGF0aC5yZXNvbHZlKHByb2Nlc3MuY3dkKCksYG5vZGVfbW9kdWxlcy9Ac2VuY2hhLyR7cGx1Z2luTmFtZX0vbm9kZV9tb2R1bGVzL0BzZW5jaGEvY21kYClcbiAgICB2YXIgY21kUGtnID0gKGZzLmV4aXN0c1N5bmMoY21kUGF0aCsnL3BhY2thZ2UuanNvbicpICYmIEpTT04ucGFyc2UoZnMucmVhZEZpbGVTeW5jKGNtZFBhdGgrJy9wYWNrYWdlLmpzb24nLCAndXRmLTgnKSkgfHwge30pO1xuICAgIHYuY21kVmVyc2lvbiA9IGNtZFBrZy52ZXJzaW9uX2Z1bGxcbiAgfVxuXG4gICBpZiAoZnJhbWV3b3JrTmFtZSAhPSB1bmRlZmluZWQgJiYgZnJhbWV3b3JrTmFtZSAhPSAnZXh0anMnKSB7XG4gICAgdmFyIGZyYW1ld29ya1BhdGggPSAnJ1xuICAgIGlmIChmcmFtZXdvcmtOYW1lID09ICdyZWFjdCcpIHtcbiAgICAgIGZyYW1ld29ya1BhdGggPSBwYXRoLnJlc29sdmUocHJvY2Vzcy5jd2QoKSwnbm9kZV9tb2R1bGVzL3JlYWN0JylcbiAgICB9XG4gICAgaWYgKGZyYW1ld29ya05hbWUgPT0gJ2FuZ3VsYXInKSB7XG4gICAgICBmcmFtZXdvcmtQYXRoID0gcGF0aC5yZXNvbHZlKHByb2Nlc3MuY3dkKCksJ25vZGVfbW9kdWxlcy9AYW5ndWxhci9jb3JlJylcbiAgICB9XG4gICAgdmFyIGZyYW1ld29ya1BrZyA9IChmcy5leGlzdHNTeW5jKGZyYW1ld29ya1BhdGgrJy9wYWNrYWdlLmpzb24nKSAmJiBKU09OLnBhcnNlKGZzLnJlYWRGaWxlU3luYyhmcmFtZXdvcmtQYXRoKycvcGFja2FnZS5qc29uJywgJ3V0Zi04JykpIHx8IHt9KTtcbiAgICB2LmZyYW1ld29ya1ZlcnNpb24gPSBmcmFtZXdvcmtQa2cudmVyc2lvblxuICAgIGlmICh2LmZyYW1ld29ya1ZlcnNpb24gPT0gdW5kZWZpbmVkKSB7XG4gICAgICBmcmFtZXdvcmtJbmZvID0gJywgJyArIGZyYW1ld29ya05hbWVcbiAgICB9XG4gICAgZWxzZSB7XG4gICAgICBmcmFtZXdvcmtJbmZvID0gJywgJyArIGZyYW1ld29ya05hbWUgKyAnIHYnICsgdi5mcmFtZXdvcmtWZXJzaW9uXG4gICAgfVxuICB9XG4gIHJldHVybiAnZXh0LXdlYnBhY2stcGx1Z2luIHYnICsgdi5wbHVnaW5WZXJzaW9uICsgJywgRXh0IEpTIHYnICsgdi5leHRWZXJzaW9uICsgJyAnICsgdi5lZGl0aW9uICsgJyBFZGl0aW9uLCBTZW5jaGEgQ21kIHYnICsgdi5jbWRWZXJzaW9uICsgJywgd2VicGFjayB2JyArIHYud2VicGFja1ZlcnNpb24gKyBmcmFtZXdvcmtJbmZvXG5cbn1cbmNhdGNoIChlKSB7XG4gIHJldHVybiAnZXh0LXdlYnBhY2stcGx1Z2luIHYnICsgdi5wbHVnaW5WZXJzaW9uICsgJywgRXh0IEpTIHYnICsgdi5leHRWZXJzaW9uICsgJyAnICsgdi5lZGl0aW9uICsgJyBFZGl0aW9uLCBTZW5jaGEgQ21kIHYnICsgdi5jbWRWZXJzaW9uICsgJywgd2VicGFjayB2JyArIHYud2VicGFja1ZlcnNpb24gKyBmcmFtZXdvcmtJbmZvXG59XG5cbn1cblxuLy8qKioqKioqKioqXG5leHBvcnQgZnVuY3Rpb24gbG9nKGFwcCxtZXNzYWdlKSB7XG4gIHZhciBzID0gYXBwICsgbWVzc2FnZVxuICByZXF1aXJlKCdyZWFkbGluZScpLmN1cnNvclRvKHByb2Nlc3Muc3Rkb3V0LCAwKVxuICB0cnkge3Byb2Nlc3Muc3Rkb3V0LmNsZWFyTGluZSgpfWNhdGNoKGUpIHt9XG4gIHByb2Nlc3Muc3Rkb3V0LndyaXRlKHMpO3Byb2Nlc3Muc3Rkb3V0LndyaXRlKCdcXG4nKVxufVxuXG4vLyoqKioqKioqKipcbmV4cG9ydCBmdW5jdGlvbiBsb2doKGFwcCxtZXNzYWdlKSB7XG4gIHZhciBoID0gZmFsc2VcbiAgdmFyIHMgPSBhcHAgKyBtZXNzYWdlXG4gIGlmIChoID09IHRydWUpIHtcbiAgICByZXF1aXJlKCdyZWFkbGluZScpLmN1cnNvclRvKHByb2Nlc3Muc3Rkb3V0LCAwKVxuICAgIHRyeSB7XG4gICAgICBwcm9jZXNzLnN0ZG91dC5jbGVhckxpbmUoKVxuICAgIH1cbiAgICBjYXRjaChlKSB7fVxuICAgIHByb2Nlc3Muc3Rkb3V0LndyaXRlKHMpXG4gICAgcHJvY2Vzcy5zdGRvdXQud3JpdGUoJ1xcbicpXG4gIH1cbn1cblxuLy8qKioqKioqKioqXG5leHBvcnQgZnVuY3Rpb24gbG9ndih2ZXJib3NlLCBzKSB7XG4gIGlmICh2ZXJib3NlID09ICd5ZXMnKSB7XG4gICAgcmVxdWlyZSgncmVhZGxpbmUnKS5jdXJzb3JUbyhwcm9jZXNzLnN0ZG91dCwgMClcbiAgICB0cnkge1xuICAgICAgcHJvY2Vzcy5zdGRvdXQuY2xlYXJMaW5lKClcbiAgICB9XG4gICAgY2F0Y2goZSkge31cbiAgICBwcm9jZXNzLnN0ZG91dC53cml0ZShgLXZlcmJvc2U6ICR7c31gKVxuICAgIHByb2Nlc3Muc3Rkb3V0LndyaXRlKCdcXG4nKVxuICB9XG59XG5cbmZ1bmN0aW9uIF9nZXRWYWxpZGF0ZU9wdGlvbnMoKSB7XG4gIHJldHVybiB7XG4gICAgXCJ0eXBlXCI6IFwib2JqZWN0XCIsXG4gICAgXCJwcm9wZXJ0aWVzXCI6IHtcbiAgICAgIFwiZnJhbWV3b3JrXCI6IHtcbiAgICAgICAgXCJ0eXBlXCI6IFtcInN0cmluZ1wiXVxuICAgICAgfSxcbiAgICAgIFwidG9vbGtpdFwiOiB7XG4gICAgICAgIFwidHlwZVwiOiBbXCJzdHJpbmdcIl1cbiAgICAgIH0sXG4gICAgICBcInRoZW1lXCI6IHtcbiAgICAgICAgXCJ0eXBlXCI6IFtcInN0cmluZ1wiXVxuICAgICAgfSxcbiAgICAgIFwiZW1pdFwiOiB7XG4gICAgICAgIFwiZXJyb3JNZXNzYWdlXCI6IFwic2hvdWxkIGJlICd5ZXMnIG9yICdubycgc3RyaW5nIHZhbHVlIChOT1QgdHJ1ZSBvciBmYWxzZSlcIixcbiAgICAgICAgXCJ0eXBlXCI6IFtcInN0cmluZ1wiXVxuICAgICAgfSxcbiAgICAgIFwic2NyaXB0XCI6IHtcbiAgICAgICAgXCJ0eXBlXCI6IFtcInN0cmluZ1wiXVxuICAgICAgfSxcbiAgICAgIFwicG9ydFwiOiB7XG4gICAgICAgIFwidHlwZVwiOiBbXCJpbnRlZ2VyXCJdXG4gICAgICB9LFxuICAgICAgXCJwYWNrYWdlc1wiOiB7XG4gICAgICAgIFwidHlwZVwiOiBbXCJzdHJpbmdcIiwgXCJhcnJheVwiXVxuICAgICAgfSxcbiAgICAgIFwicHJvZmlsZVwiOiB7XG4gICAgICAgIFwidHlwZVwiOiBbXCJzdHJpbmdcIl1cbiAgICAgIH0sXG4gICAgICBcImVudmlyb25tZW50XCI6IHtcbiAgICAgICAgXCJlcnJvck1lc3NhZ2VcIjogXCJzaG91bGQgYmUgJ2RldmVsb3BtZW50JyBvciAncHJvZHVjdGlvbicgc3RyaW5nIHZhbHVlXCIsXG4gICAgICAgIFwidHlwZVwiOiBbXCJzdHJpbmdcIl1cbiAgICAgIH0sXG4gICAgICBcInRyZWVzaGFrZVwiOiB7XG4gICAgICAgIFwiZXJyb3JNZXNzYWdlXCI6IFwic2hvdWxkIGJlICd5ZXMnIG9yICdubycgc3RyaW5nIHZhbHVlIChOT1QgdHJ1ZSBvciBmYWxzZSlcIixcbiAgICAgICAgXCJ0eXBlXCI6IFtcInN0cmluZ1wiXVxuICAgICAgfSxcbiAgICAgIFwiYnJvd3NlclwiOiB7XG4gICAgICAgIFwiZXJyb3JNZXNzYWdlXCI6IFwic2hvdWxkIGJlICd5ZXMnIG9yICdubycgc3RyaW5nIHZhbHVlIChOT1QgdHJ1ZSBvciBmYWxzZSlcIixcbiAgICAgICAgXCJ0eXBlXCI6IFtcInN0cmluZ1wiXVxuICAgICAgfSxcbiAgICAgIFwid2F0Y2hcIjoge1xuICAgICAgICBcImVycm9yTWVzc2FnZVwiOiBcInNob3VsZCBiZSAneWVzJyBvciAnbm8nIHN0cmluZyB2YWx1ZSAoTk9UIHRydWUgb3IgZmFsc2UpXCIsXG4gICAgICAgIFwidHlwZVwiOiBbXCJzdHJpbmdcIl1cbiAgICAgIH0sXG4gICAgICBcInZlcmJvc2VcIjoge1xuICAgICAgICBcImVycm9yTWVzc2FnZVwiOiBcInNob3VsZCBiZSAneWVzJyBvciAnbm8nIHN0cmluZyB2YWx1ZSAoTk9UIHRydWUgb3IgZmFsc2UpXCIsXG4gICAgICAgIFwidHlwZVwiOiBbXCJzdHJpbmdcIl1cbiAgICAgIH0sXG4gICAgICBcImluamVjdFwiOiB7XG4gICAgICAgIFwiZXJyb3JNZXNzYWdlXCI6IFwic2hvdWxkIGJlICd5ZXMnIG9yICdubycgc3RyaW5nIHZhbHVlIChOT1QgdHJ1ZSBvciBmYWxzZSlcIixcbiAgICAgICAgXCJ0eXBlXCI6IFtcInN0cmluZ1wiXVxuICAgICAgfSxcbiAgICAgIFwiaW50ZWxsaXNoYWtlXCI6IHtcbiAgICAgICAgXCJlcnJvck1lc3NhZ2VcIjogXCJzaG91bGQgYmUgJ3llcycgb3IgJ25vJyBzdHJpbmcgdmFsdWUgKE5PVCB0cnVlIG9yIGZhbHNlKVwiLFxuICAgICAgICBcInR5cGVcIjogW1wic3RyaW5nXCJdXG4gICAgICB9LFxuICAgIH0sXG4gICAgXCJhZGRpdGlvbmFsUHJvcGVydGllc1wiOiBmYWxzZVxuICB9O1xufVxuXG5cbmZ1bmN0aW9uIF9nZXREZWZhdWx0T3B0aW9ucygpIHtcbiAgcmV0dXJuIHtcbiAgICBmcmFtZXdvcms6ICdleHRqcycsXG4gICAgdG9vbGtpdDogJ21vZGVybicsXG4gICAgdGhlbWU6ICd0aGVtZS1tYXRlcmlhbCcsXG4gICAgZW1pdDogJ3llcycsXG4gICAgc2NyaXB0OiBudWxsLFxuICAgIHBvcnQ6IDE5NjIsXG4gICAgcGFja2FnZXM6IFtdLFxuXG4gICAgcHJvZmlsZTogJycsXG4gICAgZW52aXJvbm1lbnQ6ICdkZXZlbG9wbWVudCcsXG4gICAgdHJlZXNoYWtlOiAnbm8nLFxuICAgIGJyb3dzZXI6ICd5ZXMnLFxuICAgIHdhdGNoOiAneWVzJyxcbiAgICB2ZXJib3NlOiAnbm8nLFxuICAgIGluamVjdDogJ3llcycsXG4gICAgaW50ZWxsaXNoYWtlOiAneWVzJ1xuICB9XG59XG4iXX0=