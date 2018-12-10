const { __ } = wp.i18n;
const {	registerBlockType } = wp.blocks;
const { SelectControl, PanelBody } = wp.components;
const {	InspectorControls } = wp.editor;
const {	formOutline, locationsEnabled, locations } = rtb_blocks;

registerBlockType( 'restaurant-reservations/booking-form', {
	title: __( 'Booking Form', 'restaurant-reservations' ),
	icon: 'calendar',
	category: 'widgets',
	attributes: {
		location: {
			type: 'number',
			default: 0
		}
	},
	supports: {
		html: false,
		reusable: false,
		multiple: false,
	},
	edit( { attributes, setAttributes } ) {
		const { location } = attributes;

		function setLocation( location ) {
			setAttributes( { location: location } );
		}

		return (
			<div class="rtb-block-outline">
				{locationsEnabled ? (
					<InspectorControls>
						 <PanelBody>
							<SelectControl
								label={ __( 'Location' ) }
								value={ location }
								onChange={ setLocation }
								options={ locations }
							/>
						</PanelBody>
					</InspectorControls>
				) : '' }
				{formOutline.map( (fields) => {
					return (
						<div className="rtb-block-outline-fieldset">
							{fields.map( (field) => {
								return (
									<div className={'rtb-block-outline-field ' + field}>
										<div className="rtb-block-outline-label"></div>
										<div className="rtb-block-outline-input"></div>
									</div>
								)
							})}
						</div>
					)
				})}
				<div class="rtb-block-outline-button">
					{ __('Request Booking', 'restaurant-reservations') }
				</div>
			</div>
		);
	},
	save() {
		return null;
	},
} );
