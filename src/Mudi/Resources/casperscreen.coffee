casper  = require("casper").create();

viewportSizes = [
    #[320,480],
    #[320,568],
    #[600,1024],
    [1024,768],
    #[1280,800],
    #[1440,900]
]

casper.echo casper.cli.args

url 	= casper.cli.args[0];
output_dir 	= casper.cli.args[1]; 
filename = casper.cli.args[0].split('/').pop().replace(' ', '_')

casper.start()

casper.each viewportSizes, (self, size, i) -> 
	w = size[0]
	h = size[1]
	@viewport(w,h)

	@open(url).then  ->
		path = "#{output_dir}/screenshot-#{filename}-#{w}x#{h}.png"
		@echo 'this is the path... ' +  path
		@captureSelector path, 'html'

casper.run ->
	@exit()