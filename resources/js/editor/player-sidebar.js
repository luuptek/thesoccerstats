import { SelectControl, TextControl } from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';

const PlayerSidebar = () => {
	const postType = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostType(), [] );

	if ( postType !== 'tss-players' ) {
		return null;
	}

	const [ meta, setMeta ] = useEntityProp( 'postType', 'tss-players', 'meta' );

	const updateMeta = ( key, value ) => setMeta( { ...meta, [ key ]: value } );

	return (
		<PluginDocumentSettingPanel name="tss-player-data" title={ __( 'Player Data', 'tss' ) } className="tss-editor-panel">
			<TextControl
				label={ __( 'Shirt Number', 'tss' ) }
				type="number"
				value={ meta.tss_shirt_number || 0 }
				onChange={ ( value ) => updateMeta( 'tss_shirt_number', Number( value ) || 0 ) }
			/>
			<SelectControl
				label={ __( 'Position', 'tss' ) }
				value={ meta.tss_position || '' }
				options={ [
					{ label: __( 'Select', 'tss' ), value: '' },
					{ label: __( 'Goalkeeper', 'tss' ), value: 'goalkeeper' },
					{ label: __( 'Defender', 'tss' ), value: 'defender' },
					{ label: __( 'Midfield', 'tss' ), value: 'midfield' },
					{ label: __( 'Striker', 'tss' ), value: 'striker' },
				] }
				onChange={ ( value ) => updateMeta( 'tss_position', value ) }
			/>
			<TextControl
				label={ __( 'Date of Birth', 'tss' ) }
				type="date"
				value={ meta.tss_date_of_birth || '' }
				onChange={ ( value ) => updateMeta( 'tss_date_of_birth', value ) }
			/>
			<TextControl
				label={ __( 'Place of Birth', 'tss' ) }
				value={ meta.tss_place_of_birth || '' }
				onChange={ ( value ) => updateMeta( 'tss_place_of_birth', value ) }
			/>
			<TextControl
				label={ __( 'Height', 'tss' ) }
				value={ meta.tss_height || '' }
				onChange={ ( value ) => updateMeta( 'tss_height', value ) }
			/>
			<TextControl
				label={ __( 'Weight', 'tss' ) }
				value={ meta.tss_weight || '' }
				onChange={ ( value ) => updateMeta( 'tss_weight', value ) }
			/>
			<TextControl
				label={ __( 'Previous Clubs', 'tss' ) }
				value={ meta.tss_previous_clubs || '' }
				onChange={ ( value ) => updateMeta( 'tss_previous_clubs', value ) }
			/>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'tss-player-sidebar', {
	render: PlayerSidebar,
} );
