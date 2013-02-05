/* =========================================================
 * bootstrap-datepicker.js
 * http://www.eyecon.ro/bootstrap-datepicker
 * =========================================================
 * Copyright 2012 Stefan Petre
 * Improvements by Andrew Rowls
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * =========================================================
 * Sigsiu.NET - Tue, Nov 6, 2012 10:57:50
 * Fits for mootools-more compat
 * Renamed to prevent name conflicts
 * extended to include time
 * added time to date - partially based on Gus' version
 * ========================================================= */

!function ()
{
	function UTCDate()
	{
		return new Date( Date.UTC.apply( Date, arguments ) );
	}

	function UTCToday()
	{
		var today = new Date();
		return UTCDate( today.getUTCFullYear(), today.getUTCMonth(), today.getUTCDate() );
	}

	var SPDatepicker = function ( element, options )
	{
		var proxy = this;
		this.element = SobiPro.jQuery( element );
		this.language = options.language || this.element.data( 'date-language' ) || "en";
		this.language = this.language in dates ? this.language : "en";
//		this.format = DPGlobal.parseFormat( options.format || this.element.data( 'date-format' ) || 'mm/dd/yyyy' );
		this.format = options.format || SobiPro.jQuery( element ).data( "date-format" ) || 'mm/dd/yyyy hh:ii:ss';
		this.picker = SobiPro.jQuery( DPGlobal.template )
			.appendTo( SobiPro.jQuery( '#SobiPro' ) )
			.on( {
				click:SobiPro.jQuery.proxy( this.click, this )
			} );
		this.isInput = this.element.is( 'input' );
		this.component = this.element.is( '.date' ) ? this.element.find( '.add-on' ) : false;
		this.hasInput = this.component && this.element.find( 'input' ).length;
		this.ident = this.element.find( 'input' ).length ? this.element.find( 'input' ).attr( 'name' ) : 'undef';
		if ( this.component && this.component.length === 0 ) {
			this.component = false;
		}

		if ( this.isInput ) {
			this.element.on( {
				focus:SobiPro.jQuery.proxy( this.show, this ),
				keyup:SobiPro.jQuery.proxy( this.update, this ),
				blur:SobiPro.jQuery.proxy( this.update, this ),
				keydown:SobiPro.jQuery.proxy( this.keydown, this )
			} );
		}
		else {
			if ( this.component && this.hasInput ) {
				// For components that are not readonly, allow keyboard nav
				this.element.find( 'input' ).on( {
					focus:SobiPro.jQuery.proxy( this.show, this ),
					keyup:SobiPro.jQuery.proxy( this.update, this ),
					keydown:SobiPro.jQuery.proxy( this.keydown, this )
				} );

				this.component.on( 'click', SobiPro.jQuery.proxy( this.show, this ) );
			}
			else {
				this.element.on( 'click', SobiPro.jQuery.proxy( this.show, this ) );
			}
		}

		SobiPro.jQuery( document ).mousedown( function ( e )
		{
			// Clicked outside the datepicker, hide it
			if ( SobiPro.jQuery( e.target ).closest( '.datepicker' ).length == 0 ) {
				proxy.hide();
			}
		} );

		this.autoclose = true;
		if ( 'autoclose' in options ) {
			this.autoclose = options.autoclose;
		}
		else if ( 'dateAutoclose' in this.element.data() ) {
			this.autoclose = this.element.data( 'date-autoclose' );
		}

		this.keyboardNavigation = true;
		if ( 'keyboardNavigation' in options ) {
			this.keyboardNavigation = options.keyboardNavigation;
		}
		else if ( 'dateKeyboardNavigation' in this.element.data() ) {
			this.keyboardNavigation = this.element.data( 'date-keyboard-navigation' );
		}

		switch ( options.startView || this.element.data( 'date-start-view' ) ) {
			case 2:
			case 'decade':
				this.viewMode = this.startViewMode = 2;
				break;
			case 1:
			case 'year':
				this.viewMode = this.startViewMode = 1;
				break;
			case 0:
			case 'month':
			default:
				this.viewMode = this.startViewMode = 0;
				break;
		}
		this.todayBtn = (options.todayBtn || this.element.data( 'date-today-btn' ) || true);
		this.todayHighlight = (options.todayHighlight || this.element.data( 'date-today-highlight' ) || false);
		this.weekStart = ((options.weekStart || this.element.data( 'date-weekstart' ) || dates[this.language].weekStart || 0) % 7);
		this.weekEnd = ((this.weekStart + 6) % 7);
		this.startDate = -Infinity;
		this.endDate = Infinity;
		this.setStartDate( options.startDate || this.element.data( 'date-startdate' ) );
		this.setEndDate( options.endDate || this.element.data( 'date-enddate' ) );
		this.fillDow();
		this.fillMonths();
		this.update();
		this.showMode();
	};
	SPDatepicker.prototype = {
		constructor:SPDatepicker,
		time:{},
		saveTime:true,
		show:function ( e )
		{
			/* after the initialisation it should be set to false so only a "keyup" event can change the time */
			this.saveTime = false;
			this.picker.show();
			this.height = this.component ? this.component.outerHeight() : this.element.outerHeight();
			this.update();
			this.place();
			SobiPro.jQuery( window ).on( 'resize', SobiPro.jQuery.proxy( this.place, this ) );
			if ( e ) {
				e.stopPropagation();
				e.preventDefault();
			}
			this.element.trigger( {
				type:'show',
				date:this.date
			} );
		},

		hide:function ( e )
		{
			this.picker.hide();
			SobiPro.jQuery( window ).off( 'resize', this.place );
			this.viewMode = this.startViewMode;
			this.showMode();
			if ( !this.isInput ) {
				SobiPro.jQuery( document ).off( 'mousedown', this.hide );
			}
			if ( e && e.currentTarget.value )
				this.setValue();
			/* for some reason it is hiding the whole input box as well
			 * probably only Joomla! problem caused by mt or something similar
			 */
//			this.element.trigger( {
//				type:'hide',
//				date:this.date
//			} );
		},

		getDate:function ()
		{
			var d = this.getUTCDate();
			return new Date( d.getTime() + (d.getTimezoneOffset() * 60000) )
		},

		getUTCDate:function ()
		{
			return this.date;
		},

		setDate:function ( d )
		{
			this.setUTCDate( new Date( d.getTime() - (d.getTimezoneOffset() * 60000) ) );
		},

		setUTCDate:function ( d )
		{
			this.date = d;
			this.setValue();
		},

		setValue:function ()
		{
			var formatted = DPGlobal.formatDate( this.date, this.format, this.language );
			if ( !this.isInput ) {
				if ( this.component ) {
					this.element.find( 'input' ).prop( 'value', formatted );
				}
				this.element.data( 'date', formatted );
			}
			else {
				this.element.prop( 'value', formatted );
			}
		},

		setStartDate:function ( startDate )
		{
			this.startDate = startDate || -Infinity;
			if ( this.startDate !== -Infinity ) {
				this.startDate = DPGlobal.parseDate( this.startDate, this.format );
			}
			this.update();
			this.updateNavArrows();
		},

		setEndDate:function ( endDate )
		{
			this.endDate = endDate || Infinity;
			if ( this.endDate !== Infinity ) {
				this.endDate = DPGlobal.parseDate( this.endDate, this.format );
			}
			this.update();
			this.updateNavArrows();
		},

		place:function ()
		{
			var zIndex = parseInt( this.element.parents().filter(function ()
			{
				return SobiPro.jQuery( this ).css( 'z-index' ) != 'auto';
			} ).first().css( 'z-index' ) ) + 10;
			var offset = this.component ? this.component.offset() : this.element.offset();
			this.picker.css( {
				top:offset.top + this.height,
				left:offset.left,
				zIndex:zIndex
			} );
		},

		update:function ( e )
		{
			if ( !( this.time[ this.ident ]) ) {
				this.time[ this.ident ] = {'hours':00, 'minutes':00, 'seconds':00 };
			}
			try {
				if ( e.type == 'keyup' ) {
					this.saveTime = true;
				}
			}
			catch ( e ) {
			}
			// 				this.isInput ? this.element.prop( 'value' ) : this.element.data( 'date' ) || this.element.find( 'input' ).prop( 'value' ),
			this.date = DPGlobal.parseDate(
				this.element.find( 'input' ).prop( 'value' ),
				this.format, this.time[ this.ident ], this.saveTime
			);
			if ( this.date < this.startDate ) {
				this.viewDate = new Date( this.startDate );
			}
			else if ( this.date > this.endDate ) {
				this.viewDate = new Date( this.endDate );
			}
			else {
				this.viewDate = new Date( this.date );
			}
//			SobiPro.DebOut(this.date);
			this.fill();
			this.element.trigger( {
				type:'changeDate',
				date:this.date
			} );
		},

		fillDow:function ()
		{
			var dowCnt = this.weekStart;
			var html = '<tr>';
			while ( dowCnt < this.weekStart + 7 ) {
				html += '<th class="dow">' + dates[this.language].daysMin[(dowCnt++) % 7] + '</th>';
			}
			html += '</tr>';
			this.picker.find( '.datepicker-days thead' ).append( html );
		},

		fillMonths:function ()
		{
			var html = '';
			var i = 0;
			while ( i < 12 ) {
				html += '<span class="month">' + dates[this.language].monthsShort[i++] + '</span>';
			}
			this.picker.find( '.datepicker-months td' ).html( html );
		},

		fill:function ()
		{
			var d = new Date( this.viewDate ),
				year = d.getUTCFullYear(),
				month = d.getUTCMonth(),
				startYear = this.startDate !== -Infinity ? this.startDate.getUTCFullYear() : -Infinity,
				startMonth = this.startDate !== -Infinity ? this.startDate.getUTCMonth() : -Infinity,
				endYear = this.endDate !== Infinity ? this.endDate.getUTCFullYear() : Infinity,
				endMonth = this.endDate !== Infinity ? this.endDate.getUTCMonth() : Infinity,
				currentDate = this.date.valueOf(),
				today = new Date();
			this.picker.find( '.datepicker-days thead th:eq(1)' )
				.text( dates[this.language].months[month] + ' ' + year );
			this.picker.find( 'tfoot th.today' )
				.text( dates[this.language].today )
				.toggle( this.todayBtn );
			this.updateNavArrows();
			this.fillMonths();
			var prevMonth = UTCDate( year, month - 1, 28, 0, 0, 0, 0 ),
				day = DPGlobal.getDaysInMonth( prevMonth.getUTCFullYear(), prevMonth.getUTCMonth() );
			prevMonth.setUTCDate( day );
			prevMonth.setUTCDate( day - (prevMonth.getUTCDay() - this.weekStart + 7) % 7 );
			var nextMonth = new Date( prevMonth );
			nextMonth.setUTCDate( nextMonth.getUTCDate() + 42 );
			nextMonth = nextMonth.valueOf();
			var html = [];
			var clsName;
			while ( prevMonth.valueOf() < nextMonth ) {
				if ( prevMonth.getUTCDay() == this.weekStart ) {
					html.push( '<tr>' );
				}
				clsName = '';
				if ( prevMonth.getUTCFullYear() < year || (prevMonth.getUTCFullYear() == year && prevMonth.getUTCMonth() < month) ) {
					clsName += ' old';
				}
				else if ( prevMonth.getUTCFullYear() > year || (prevMonth.getUTCFullYear() == year && prevMonth.getUTCMonth() > month) ) {
					clsName += ' new';
				}
				// Compare internal UTC date with local today, not UTC today
				if ( this.todayHighlight &&
					prevMonth.getUTCFullYear() == today.getFullYear() &&
					prevMonth.getUTCMonth() == today.getMonth() &&
					prevMonth.getUTCDate() == today.getDate() ) {
					clsName += ' today';
				}
				/** no questions here */
//				if ( prevMonth.getDay() == currentDate ) {
				try {
					if ( ( prevMonth.getDay() == this.date.getDay() ) && ( prevMonth.getWeek() == this.date.getWeek() ) ) {
						clsName += ' active';
					}
				}
				catch ( e ) {
				}
				if ( prevMonth.valueOf() < this.startDate || prevMonth.valueOf() > this.endDate ) {
					clsName += ' disabled';
				}
				html.push( '<td class="day' + clsName + '">' + prevMonth.getUTCDate() + '</td>' );
				if ( prevMonth.getUTCDay() == this.weekEnd ) {
					html.push( '</tr>' );
				}
				prevMonth.setUTCDate( prevMonth.getUTCDate() + 1 );
			}
			this.picker.find( '.datepicker-days tbody' ).empty().append( html.join( '' ) );
			var currentYear = this.date.getUTCFullYear();

			var months = this.picker.find( '.datepicker-months' )
				.find( 'th:eq(1)' )
				.text( year )
				.end()
				.find( 'span' ).removeClass( 'active' );
			if ( currentYear == year ) {
				months.eq( this.date.getUTCMonth() ).addClass( 'active' );
			}
			if ( year < startYear || year > endYear ) {
				months.addClass( 'disabled' );
			}
			if ( year == startYear ) {
				months.slice( 0, startMonth ).addClass( 'disabled' );
			}
			if ( year == endYear ) {
				months.slice( endMonth + 1 ).addClass( 'disabled' );
			}

			html = '';
			year = parseInt( year / 10, 10 ) * 10;
			var yearCont = this.picker.find( '.datepicker-years' )
				.find( 'th:eq(1)' )
				.text( year + '-' + (year + 9) )
				.end()
				.find( 'td' );
			year -= 1;
			for ( var i = -1; i < 11; i++ ) {
				html += '<span class="year' + (i == -1 || i == 10 ? ' old' : '') + (currentYear == year ? ' active' : '') + (year < startYear || year > endYear ? ' disabled' : '') + '">' + year + '</span>';
				year += 1;
			}
			yearCont.html( html );
		},

		updateNavArrows:function ()
		{
			var d = new Date( this.viewDate ),
				year = d.getUTCFullYear(),
				month = d.getUTCMonth();
			switch ( this.viewMode ) {
				case 0:
					if ( this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear() && month <= this.startDate.getUTCMonth() ) {
						this.picker.find( '.prev' ).css( {visibility:'hidden'} );
					}
					else {
						this.picker.find( '.prev' ).css( {visibility:'visible'} );
					}
					if ( this.endDate !== Infinity && year >= this.endDate.getUTCFullYear() && month >= this.endDate.getUTCMonth() ) {
						this.picker.find( '.next' ).css( {visibility:'hidden'} );
					}
					else {
						this.picker.find( '.next' ).css( {visibility:'visible'} );
					}
					break;
				case 1:
				case 2:
					if ( this.startDate !== -Infinity && year <= this.startDate.getUTCFullYear() ) {
						this.picker.find( '.prev' ).css( {visibility:'hidden'} );
					}
					else {
						this.picker.find( '.prev' ).css( {visibility:'visible'} );
					}
					if ( this.endDate !== Infinity && year >= this.endDate.getUTCFullYear() ) {
						this.picker.find( '.next' ).css( {visibility:'hidden'} );
					}
					else {
						this.picker.find( '.next' ).css( {visibility:'visible'} );
					}
					break;
			}
		},

		click:function ( e )
		{
			e.stopPropagation();
			e.preventDefault();
			var target = SobiPro.jQuery( e.target ).closest( 'span, td, th' );
			if ( target.length == 1 ) {
				switch ( target[0].nodeName.toLowerCase() ) {
					case 'th':
						switch ( target[0].className ) {
							case 'switch':
								this.showMode( 1 );
								break;
							case 'prev':
							case 'next':
								var dir = DPGlobal.modes[this.viewMode].navStep * (target[0].className == 'prev' ? -1 : 1);
								switch ( this.viewMode ) {
									case 0:
										this.viewDate = this.moveMonth( this.viewDate, dir );
										break;
									case 1:
									case 2:
										this.viewDate = this.moveYear( this.viewDate, dir );
										break;
								}
								this.fill();
								break;
							case 'today':
								var date = new Date();
								date.setUTCMilliseconds( 0 );
								this.showMode( -2 );
								var which = this.todayBtn == 'linked' ? null : 'view';
								this.time[ this.ident ] = { 'hours':date.getHours(), 'minutes':date.getMinutes(), 'seconds':date.getSeconds() };
								this.date = DPGlobal.parseDate( date, this.format, this.time[ this.ident ], true );
								this._setDate( date, which );
								break;
						}
						break;
					case 'span':
						if ( !target.is( '.disabled' ) ) {
							this.viewDate.setUTCDate( 1 );
							if ( target.is( '.month' ) ) {
								var month = target.parent().find( 'span' ).index( target );
								this.viewDate.setUTCMonth( month );
								this.element.trigger( {
									type:'changeMonth',
									date:this.viewDate
								} );
							}
							else {
								var year = parseInt( target.text(), 10 ) || 0;
								this.viewDate.setUTCFullYear( year );
								this.element.trigger( {
									type:'changeYear',
									date:this.viewDate
								} );
							}
							this.showMode( -1 );
							this.fill();
						}
						break;
					// this is when a date has been selected in the picker << this is a comment for Gods' sake
					case 'td':
						if ( target.is( '.day' ) && !target.is( '.disabled' ) ) {
							this.saveTime = false;
							var day = parseInt( target.text(), 10 ) || 1;
							var year = this.viewDate.getUTCFullYear(),
								month = this.viewDate.getUTCMonth();
							if ( target.is( '.old' ) ) {
								if ( month == 0 ) {
									month = 11;
									year -= 1;
								}
								else {
									month -= 1;
								}
							}
							else if ( target.is( '.new' ) ) {
								if ( month == 11 ) {
									month = 0;
									year += 1;
								}
								else {
									month += 1;
								}
							}
							var setDate = UTCDate( year, month, day, 0, 0, 0, 0 );
							storedTime = this.time[ this.ident ];
							if ( storedTime.hours ) {
								setDate.setHours( storedTime.hours );
							}
							if ( storedTime.minutes ) {
								setDate.setMinutes( storedTime.minutes );
							}
							if ( storedTime.seconds ) {
								setDate.setSeconds( storedTime.seconds );
							}
							this._setDate( setDate );
						}
						break;
				}
			}
		},

		_setDate:function ( date, which )
		{
			if ( !which || which == 'date' )
				this.date = date;
			if ( !which || which == 'view' )
				this.viewDate = date;
			this.fill();
			this.setValue();
			this.element.trigger( {
				type:'changeDate',
				date:this.date
			} );
			var element;
			if ( this.isInput ) {
				element = this.element;
			}
			else if ( this.component ) {
				element = this.element.find( 'input' );
			}
			if ( element ) {
				element.change();
				if ( this.autoclose ) {
					this.hide();
				}
			}
		},

		moveMonth:function ( date, dir )
		{
			if ( !dir ) return date;
			var new_date = new Date( date.valueOf() ),
				day = new_date.getUTCDate(),
				month = new_date.getUTCMonth(),
				mag = Math.abs( dir ),
				new_month, test;
			dir = dir > 0 ? 1 : -1;
			if ( mag == 1 ) {
				test = dir == -1
					// If going back one month, make sure month is not current month
					// (eg, Mar 31 -> Feb 31 == Feb 28, not Mar 02)
					? function ()
				{
					return new_date.getUTCMonth() == month;
				}
					// If going forward one month, make sure month is as expected
					// (eg, Jan 31 -> Feb 31 == Feb 28, not Mar 02)
					: function ()
				{
					return new_date.getUTCMonth() != new_month;
				};
				new_month = month + dir;
				new_date.setUTCMonth( new_month );
				// Dec -> Jan (12) or Jan -> Dec (-1) -- limit expected date to 0-11
				if ( new_month < 0 || new_month > 11 )
					new_month = (new_month + 12) % 12;
			}
			else {
				// For magnitudes >1, move one month at a time...
				for ( var i = 0; i < mag; i++ )
					// ...which might decrease the day (eg, Jan 31 to Feb 28, etc)...
					new_date = this.moveMonth( new_date, dir );
				// ...then reset the day, keeping it in the new month
				new_month = new_date.getUTCMonth();
				new_date.setUTCDate( day );
				test = function ()
				{
					return new_month != new_date.getUTCMonth();
				};
			}
			// Common date-resetting loop -- if date is beyond end of month, make it
			// end of month
			while ( test() ) {
				new_date.setUTCDate( --day );
				new_date.setUTCMonth( new_month );
			}
			return new_date;
		},

		moveYear:function ( date, dir )
		{
			return this.moveMonth( date, dir * 12 );
		},

		dateWithinRange:function ( date )
		{
			return date >= this.startDate && date <= this.endDate;
		},

		keydown:function ( e )
		{
			if ( this.picker.is( ':not(:visible)' ) ) {
				if ( e.keyCode == 27 ) // allow escape to hide and re-show picker
					this.show();
				return;
			}
			var dateChanged = false,
				dir, day, month,
				newDate, newViewDate;
			switch ( e.keyCode ) {
				case 27: // escape
					this.hide();
					e.preventDefault();
					break;
				case 37: // left
				case 39: // right
					if ( !this.keyboardNavigation ) break;
					dir = e.keyCode == 37 ? -1 : 1;
					if ( e.ctrlKey ) {
						newDate = this.moveYear( this.date, dir );
						newViewDate = this.moveYear( this.viewDate, dir );
					}
					else if ( e.shiftKey ) {
						newDate = this.moveMonth( this.date, dir );
						newViewDate = this.moveMonth( this.viewDate, dir );
					}
					else {
						newDate = new Date( this.date );
						newDate.setUTCDate( this.date.getUTCDate() + dir );
						newViewDate = new Date( this.viewDate );
						newViewDate.setUTCDate( this.viewDate.getUTCDate() + dir );
					}
					if ( this.dateWithinRange( newDate ) ) {
						this.date = newDate;
						this.viewDate = newViewDate;
						this.setValue();
						this.update();
						e.preventDefault();
						dateChanged = true;
					}
					break;
				case 38: // up
				case 40: // down
					if ( !this.keyboardNavigation ) break;
					dir = e.keyCode == 38 ? -1 : 1;
					if ( e.ctrlKey ) {
						newDate = this.moveYear( this.date, dir );
						newViewDate = this.moveYear( this.viewDate, dir );
					}
					else if ( e.shiftKey ) {
						newDate = this.moveMonth( this.date, dir );
						newViewDate = this.moveMonth( this.viewDate, dir );
					}
					else {
						newDate = new Date( this.date );
						newDate.setUTCDate( this.date.getUTCDate() + dir * 7 );
						newViewDate = new Date( this.viewDate );
						newViewDate.setUTCDate( this.viewDate.getUTCDate() + dir * 7 );
					}
					if ( this.dateWithinRange( newDate ) ) {
						this.date = newDate;
						this.viewDate = newViewDate;
						this.setValue();
						this.update();
						e.preventDefault();
						dateChanged = true;
					}
					break;
				case 13: // enter
					this.hide();
					e.preventDefault();
					break;
				case 9: // tab
					this.hide();
					break;
			}
			if ( dateChanged ) {
				this.element.trigger( {
					type:'changeDate',
					date:this.date
				} );
				var element;
				if ( this.isInput ) {
					element = this.element;
				}
				else if ( this.component ) {
					element = this.element.find( 'input' );
				}
				if ( element ) {
					element.change();
				}
			}
		},

		showMode:function ( dir )
		{
			if ( dir ) {
				this.viewMode = Math.max( 0, Math.min( 2, this.viewMode + dir ) );
			}
			this.picker.find( '>div' ).hide().filter( '.datepicker-' + DPGlobal.modes[this.viewMode].clsName ).show();
			this.updateNavArrows();
		}
	};

	SobiPro.jQuery.fn.spDatepicker = function ( option )
	{
		var args = Array.apply( null, arguments );
		args.shift();
		return this.each( function ()
		{
			var _this = SobiPro.jQuery( this ),
				data = _this.data( 'datepicker' ),
				options = typeof option == 'object' && option;
			if ( !data ) {
				_this.data( 'datepicker', (data = new SPDatepicker( this, SobiPro.jQuery.extend( {}, SobiPro.jQuery.fn.spDatepicker.defaults, options ) )) );
			}
			if ( typeof option == 'string' && typeof data[option] == 'function' ) {
				data[option].apply( data, args );
			}
		} );
	};

	SobiPro.jQuery.fn.spDatepicker.defaults = {
	};
	SobiPro.jQuery.fn.spDatepicker.Constructor = SPDatepicker;
	var dates = SobiPro.jQuery.fn.spDatepicker.dates = { en:spDatePickerLang };
//	{
//		en:{
//			days:["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
//			daysShort:["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
//			daysMin:["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
//			months:["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
//			monthsShort:["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
//			today:"Today"
//		}
//	};

	var DPGlobal = {
		modes:[
			{
				clsName:'days',
				navFnc:'Month',
				navStep:1
			},
			{
				clsName:'months',
				navFnc:'FullYear',
				navStep:1
			},
			{
				clsName:'years',
				navFnc:'FullYear',
				navStep:10
			}
		],
		isLeapYear:function ( year )
		{
			return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0))
		},
		getDaysInMonth:function ( year, month )
		{
			return [31, (DPGlobal.isLeapYear( year ) ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][month]
		},
		validParts:/dd?|g|G|h|s|H|i|mm?|MM?|yy(?:yy)?/g,
		nonpunctuation:/[^ -\/:-@\[-`{-~\t\n\r]+/g,
		parseFormat:function ( format )
		{
			// IE treats \0 as a string end in inputs (truncating the value),
			// so it's a bad format delimiter, anyway
			var separators = format.replace( this.validParts, '\0' ).split( '\0' );
			var parts = format.match( this.validParts );
			if ( !separators || !separators.length || !parts || parts.length == 0 ) {
				throw new Error( "Invalid date format." );
			}
			return {separators:separators, parts:parts};
		},
		parseDate:function ( dateStr, format, time, saveTime )
		{
			if ( dateStr instanceof Date ) {
				return dateStr;
			}
			if ( !( dateStr ) ) {
				this.date = 0;
				return new Date();
			}
			//convert str into date
			var strParts = dateStr.split( /[^a-zA-Z0-9]/g );
			var formatParts = format.split( /[^a-zA-Z]/g );

			date = new Date(),
				date.setHours( 0 );
			date.setMinutes( 0 );
			date.setSeconds( 0 );
			date.setMilliseconds( 0 );
			for ( var key in formatParts ) {
				if ( typeof strParts[key] != 'undefined' ) {
					val = strParts[key];
					switch ( formatParts[key] ) {
						case 'dd':
						case 'd':
							date.setDate( val );
							break;
						case 'mm':
						case 'm':
							date.setMonth( val - 1 );
							break;
						case 'yy':
							date.setFullYear( 2000 + val );
							break;
						case 'yyyy':
							date.setFullYear( val );
							break;
						case 'hh':
						case 'h':
							if ( saveTime ) {
								time.hours = val;
								date.setHours( val );
							}
							else {
								date.setHours( time.hours );
							}
							break;
						case 'ii':
						case 'i':
							if ( saveTime ) {
								time.minutes = val;
								date.setMinutes( val );
							}
							else {
								date.setMinutes( time.minutes );
							}
							break;
						case 'ss':
						case 's':
							if ( saveTime ) {
								time.seconds = val;
								date.setSeconds( val );
							}
							else {
								date.setSeconds( time.seconds );
							}
							break;
					}
				}
			}
			return date;
		},
		formatDate:function ( date, format )
		{ // build a formatted string
			var templateParts = {
				dd:(date.getDate() < 10 ? '0' : '') + date.getDate(),
				d:date.getDate(),
				mm:((date.getMonth() + 1) < 10 ? '0' : '') + (date.getMonth() + 1),
				m:date.getMonth() + 1,
				yyyy:date.getFullYear(),
				yy:date.getFullYear().toString().substring( 2 ),
				hh:(date.getHours() < 10 ? '0' : '') + date.getHours(),
				h:date.getHours(),
				ii:(date.getMinutes() < 10 ? '0' : '') + date.getMinutes(),
				i:date.getMinutes(),
				ss:(date.getSeconds() < 10 ? '0' : '') + date.getSeconds(),
				s:date.getSeconds()
			};

			var dateStr = format;

			for ( var key in templateParts ) {
				val = templateParts[key];
				dateStr = dateStr.replace( key, val );
			}

			return dateStr;
		},
		headTemplate:'<thead>' +
			'<tr>' +
			'<th class="prev"><i class="icon-arrow-left"/></th>' +
			'<th colspan="5" class="switch"></th>' +
			'<th class="next"><i class="icon-arrow-right"/></th>' +
			'</tr>' +
			'</thead>',
		contTemplate:'<tbody><tr><td colspan="7"></td></tr></tbody>',
		footTemplate:'<tfoot><tr><th colspan="7" class="today"></th></tr></tfoot>'
	};
	DPGlobal.template = '<div class="datepicker dropdown-menu hide">' +
		'<div class="datepicker-days">' +
		'<table class=" table-condensed">' +
		DPGlobal.headTemplate +
		'<tbody></tbody>' +
		DPGlobal.footTemplate +
		'</table>' +
		'</div>' +
		'<div class="datepicker-months">' +
		'<table class="table-condensed">' +
		DPGlobal.headTemplate +
		DPGlobal.contTemplate +
		DPGlobal.footTemplate +
		'</table>' +
		'</div>' +
		'<div class="datepicker-years">' +
		'<table class="table-condensed">' +
		DPGlobal.headTemplate +
		DPGlobal.contTemplate +
		DPGlobal.footTemplate +
		'</table>' +
		'</div>' +
		'</div>';
}( window.jQuery );

SobiPro.jQuery( document ).ready( function ()
{
	SobiPro.jQuery( '.spDatePicker' ).each( function ( i, e )
	{
		"use strict";
		SobiPro.jQuery( e )
			.spDatepicker()
			.on( 'changeDate', function ( ev )
			{
				var set = "";
				if ( ev.date.valueOf() && SobiPro.jQuery( ev.currentTarget ).find( ':text' ).val() ) {
					set = new Date( ev.date.valueOf() ) / 1000;
				}
				SobiPro.DebOut( set );
				SobiPro.jQuery( ev.currentTarget )
					.find( ':hidden' )
					.val( set );
			} );
	} );
} );
