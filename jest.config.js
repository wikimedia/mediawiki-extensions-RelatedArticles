'use strict';

// For a detailed explanation regarding each configuration property, visit:
// https://jestjs.io/docs/en/configuration.html

module.exports = {
	// Automatically clear mock calls and instances between every test
	clearMocks: true,

	// Indicates whether the coverage information should be collected while executing the test
	collectCoverage: true,

	// An array of glob patterns indicating a set of files for
	// which coverage information should be collected
	collectCoverageFrom: [
		'resources/**/*.(js|vue)'
	],

	// The directory where Jest should output its coverage files
	coverageDirectory: 'coverage',

	// An array of regexp pattern strings used to skip coverage collection
	coveragePathIgnorePatterns: [
		'/node_modules/',
		// currently covered by QUnit
		'/resources/ext.relatedArticles.readMore/RelatedPagesGateway.js',
		// Should not need test coverage. Covered by visual regression testing.
		'/resources/ext.relatedArticles.readMore.bootstrap/index.js'
	],

	// An object that configures minimum threshold enforcement for coverage results
	coverageThreshold: {
		global: {
			branches: 100,
			functions: 100,
			lines: 100,
			statements: 100
		}
	},

	// A set of global variables that need to be available in all test environments
	globals: {
		'vue-jest': {
			babelConfig: false,
			hideStyleWarn: true,
			experimentalCSSCompile: true
		}
	},

	// An array of file extensions your modules use
	moduleFileExtensions: [
		'js',
		'json'
	],

	// The paths to modules that run some code to configure or
	// set up the testing environment before each test
	setupFiles: [
		'./jest.setup.js'
	],
	testEnvironment: 'jsdom',
	testRegex: '/tests/jest/.*.test.js$'
};
