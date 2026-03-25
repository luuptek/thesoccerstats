import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import './editor.scss';
import './style.scss';
import metadata from './block.json';

const usePostOptions = ( postType ) => {
	const posts = useSelect(
		( select ) => select( 'core' ).getEntityRecords( 'postType', postType, { per_page: -1, orderby: 'title', order: 'asc' } ),
		[ postType ]
	);

	return [
		{ label: __( 'Select', 'tss' ), value: 0 },
		...( posts || [] ).map( ( post ) => ( {
			label: post.title.rendered || __( '(no title)', 'tss' ),
			value: post.id,
		} ) ),
	];
};

registerBlockType( metadata.name, {
	edit( { attributes, setAttributes } ) {
		const currentPostId = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostId(), [] );
		const currentPostType = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostType(), [] );
		const matchOptions = usePostOptions( 'tss-matches' );
		const blockProps = useBlockProps();
		const isCurrentMatchPage = currentPostType === 'tss-matches' && !! currentPostId;
		const previewLabel = attributes.useCurrent
			? ( isCurrentMatchPage
				? __( 'Uses the current match post automatically.', 'tss' )
				: __( 'Current post is not a match. Select a match or disable "Use Current Match".', 'tss' ) )
			: ( attributes.matchId
				? __( 'Uses the selected match.', 'tss' )
				: __( 'Select a match from block settings.', 'tss' ) );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Match Summary Settings', 'tss' ) }>
						<ToggleControl
							label={ __( 'Use Current Match', 'tss' ) }
							checked={ !! attributes.useCurrent }
							onChange={ ( value ) => setAttributes( { useCurrent: value } ) }
						/>
						{ ! attributes.useCurrent && (
							<SelectControl
								label={ __( 'Match', 'tss' ) }
								value={ attributes.matchId || 0 }
								options={ matchOptions }
								onChange={ ( value ) => setAttributes( { matchId: Number( value ) } ) }
							/>
						) }
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					<div className="tss-match-summary__editor-preview">
						<strong>{ __( 'Here Is Match Summary Block', 'tss' ) }</strong>
						<p>{ previewLabel }</p>
					</div>
				</div>
			</>
		);
	},
	save() {
		return null;
	},
} );
