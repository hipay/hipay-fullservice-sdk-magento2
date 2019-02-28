exports.config = {
    tests: './*_test.js',
    output: './output',
    helpers: {
        Puppeteer: {
            url: 'http://pi-mg2-develop.hipay-pos-platform.com/',
            show: false
        }
    },
    include: {
        I: './steps_file.js'
    },
    bootstrap: null,
    mocha: {},
    name: 'test-codeceptjs'
}
