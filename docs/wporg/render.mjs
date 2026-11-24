// Renders the WordPress.org icon and banners from icon.html and banner.html
// into .wordpress-org/. Needs Playwright: node docs/wporg/render.mjs
import { chromium } from 'playwright';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const here = path.dirname( fileURLToPath( import.meta.url ) );
const out = path.join( here, '..', '..', '.wordpress-org' );
const browser = await chromium.launch();

for ( const [ file, selector, sizes ] of [
	[ 'icon.html', '.icon', [ [ 'icon-128x128.png', 0.5 ], [ 'icon-256x256.png', 1 ] ] ],
	[ 'banner.html', '.banner', [ [ 'banner-772x250.png', 1 ], [ 'banner-1544x500.png', 2 ] ] ],
] ) {
	for ( const [ name, scale ] of sizes ) {
		const page = await browser.newPage( { deviceScaleFactor: scale } );
		await page.goto( 'file://' + path.join( here, file ) );
		await page.locator( selector ).screenshot( { path: path.join( out, name ), omitBackground: true } );
		await page.close();
	}
}

await browser.close();
