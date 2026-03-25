import apiFetch from '@wordpress/api-fetch';
import { Button, ComboboxControl, Modal, Notice, Panel, PanelBody, SelectControl, Spinner, TextareaControl, TextControl, ToggleControl, ToolbarButton } from '@wordpress/components';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { createPortal, useEffect, useRef, useState } from '@wordpress/element';
import { registerPlugin } from '@wordpress/plugins';
import { __ } from '@wordpress/i18n';

const usePostOptions = ( postType ) => {
	const posts = useSelect(
		( select ) => select( coreStore ).getEntityRecords( 'postType', postType, { per_page: -1, orderby: 'title', order: 'asc' } ),
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

const EventRows = ( { label, rows, players, onAdd, onChange, onRemove, ownGoal = false } ) => (
	<div>
		<p><strong>{ label }</strong></p>
		{ rows.map( ( row, index ) => (
			<div key={ `${ label }-${ index }` } style={ { border: '1px solid #e2e8f0', borderRadius: '8px', marginBottom: '12px', padding: '12px' } }>
				<SelectControl
					label={ __( 'Player', 'tss' ) }
					value={ row.playerId || 0 }
					options={ players }
					onChange={ ( value ) => onChange( index, 'playerId', Number( value ) ) }
				/>
				<TextControl
					label={ __( 'Minute', 'tss' ) }
					type="number"
					value={ row.minute || '' }
					onChange={ ( value ) => onChange( index, 'minute', Number( value ) || 0 ) }
				/>
				{ ownGoal && (
					<>
						<ToggleControl
							label={ __( 'Penalty', 'tss' ) }
							checked={ !! row.isPenalty }
							onChange={ ( value ) => onChange( index, 'isPenalty', value ) }
						/>
						<ToggleControl
							label={ __( 'Own Goal', 'tss' ) }
							checked={ !! row.isOwnGoal }
							onChange={ ( value ) => onChange( index, 'isOwnGoal', value ) }
						/>
						<TextControl
							label={ __( 'Own Scorer Name', 'tss' ) }
							value={ row.ownScorer || '' }
							onChange={ ( value ) => onChange( index, 'ownScorer', value ) }
						/>
					</>
				) }
				<Button isDestructive variant="secondary" onClick={ () => onRemove( index ) }>
					{ __( 'Remove', 'tss' ) }
				</Button>
			</div>
		) ) }
		<Button variant="secondary" onClick={ onAdd }>
			{ __( 'Add Row', 'tss' ) }
		</Button>
	</div>
);

const PlayerListControl = ( { label, value, options, onChange } ) => (
	<SelectControl
		multiple
		label={ label }
		value={ value }
		options={ options.filter( ( option ) => option.value !== 0 ) }
		onChange={ ( selected ) => onChange( ( selected || [] ).map( Number ) ) }
	/>
);

const SubstitutionRows = ( { rows, players, onAdd, onChange, onRemove } ) => (
	<div>
		<p><strong>{ __( 'Substitutions', 'tss' ) }</strong></p>
		{ rows.map( ( row, index ) => (
			<div key={ `substitution-${ index }` } style={ { border: '1px solid #e2e8f0', borderRadius: '8px', marginBottom: '12px', padding: '12px' } }>
				<SelectControl
					label={ __( 'Player In', 'tss' ) }
					value={ row.playerIn || 0 }
					options={ players }
					onChange={ ( value ) => onChange( index, 'playerIn', Number( value ) ) }
				/>
				<SelectControl
					label={ __( 'Player Out', 'tss' ) }
					value={ row.playerOut || 0 }
					options={ players }
					onChange={ ( value ) => onChange( index, 'playerOut', Number( value ) ) }
				/>
				<TextControl
					label={ __( 'Minute', 'tss' ) }
					type="number"
					value={ row.minute || '' }
					onChange={ ( value ) => onChange( index, 'minute', Number( value ) || 0 ) }
				/>
				<Button isDestructive variant="secondary" onClick={ () => onRemove( index ) }>
					{ __( 'Remove', 'tss' ) }
				</Button>
			</div>
		) ) }
		<Button variant="secondary" onClick={ onAdd }>
			{ __( 'Add Substitution', 'tss' ) }
		</Button>
	</div>
);

const emptyEditorData = {
	starters: [],
	substitutes: [],
	substitutions: [],
	goals: [],
	yellow_cards: [],
	red_cards: [],
};

const MatchEditorHeaderButton = ( { onClick } ) => {
	const [ target, setTarget ] = useState( null );

	useEffect( () => {
		let observer;

		const resolveTarget = () => {
			const nextTarget = document.querySelector(
				'.edit-post-header__settings, .editor-header__settings, .editor-header .components-button.has-icon'
			)?.parentElement || document.querySelector(
				'.edit-post-header__settings, .editor-header__settings'
			);

			if ( nextTarget ) {
				setTarget( nextTarget );
			}
		};

		resolveTarget();
		window.requestAnimationFrame( resolveTarget );

		observer = new MutationObserver( resolveTarget );
		observer.observe( document.body, {
			childList: true,
			subtree: true,
		} );

		return () => {
			if ( observer ) {
				observer.disconnect();
			}
		};
	}, [] );

	if ( ! target ) {
		return null;
	}

	return createPortal(
		<ToolbarButton
			icon="clipboard"
			label={ __( 'Open Match Editor', 'tss' ) }
			onClick={ onClick }
		/>,
		target
	);
};

const MatchEditorFields = ( {
	errorMessage,
	isLoading,
	isSaving,
	meta,
	editorData,
	seasons,
	matchTypes,
	opponents,
	players,
	updateMeta,
	updateEditorData,
	addRow,
	removeRow,
	updateRow,
} ) => (
	<>
		{ errorMessage && <Notice status="error" isDismissible={ false }>{ errorMessage }</Notice> }
		<div style={ { display: 'grid', gap: '16px', gridTemplateColumns: 'repeat(auto-fit, minmax(320px, 1fr))' } }>
			<div style={ { background: '#fff', border: '1px solid #d8dee8', borderRadius: '12px', padding: '16px' } }>
				<h2 style={ { marginTop: 0 } }>{ __( 'Match Meta', 'tss' ) }</h2>
				<TextControl label={ __( 'Date', 'tss' ) } type="date" value={ meta.tss_match_date || '' } onChange={ ( value ) => updateMeta( 'tss_match_date', value ) } />
				<TextControl label={ __( 'Time', 'tss' ) } value={ meta.tss_match_time || '' } onChange={ ( value ) => updateMeta( 'tss_match_time', value ) } />
				<SelectControl label={ __( 'Season', 'tss' ) } value={ meta.tss_match_season || 0 } options={ seasons } onChange={ ( value ) => updateMeta( 'tss_match_season', Number( value ) ) } />
				<SelectControl label={ __( 'Match Type', 'tss' ) } value={ meta.tss_match_matchtype || 0 } options={ matchTypes } onChange={ ( value ) => updateMeta( 'tss_match_matchtype', Number( value ) ) } />
				<TextControl label={ __( 'Additional Match Type', 'tss' ) } value={ meta.tss_match_additional_matchtype || '' } onChange={ ( value ) => updateMeta( 'tss_match_additional_matchtype', value ) } />
				<ComboboxControl
					label={ __( 'Opponent', 'tss' ) }
					value={ String( meta.tss_match_opponent || '' ) }
					options={ opponents
						.filter( ( option ) => option.value !== 0 )
						.map( ( option ) => ( {
							label: option.label,
							value: String( option.value ),
						} ) ) }
					onChange={ ( value ) => updateMeta( 'tss_match_opponent', Number( value ) || 0 ) }
				/>
				<SelectControl
					label={ __( 'Location', 'tss' ) }
					value={ meta.tss_match_location || 'home' }
					options={ [
						{ label: __( 'Home', 'tss' ), value: 'home' },
						{ label: __( 'Away', 'tss' ), value: 'away' },
						{ label: __( 'Neutral', 'tss' ), value: 'neutral' },
					] }
					onChange={ ( value ) => updateMeta( 'tss_match_location', value ) }
				/>
				<TextControl label={ __( 'Goals For', 'tss' ) } type="number" value={ meta.tss_match_goals_for || 0 } onChange={ ( value ) => updateMeta( 'tss_match_goals_for', Number( value ) || 0 ) } />
				<TextControl label={ __( 'Goals Against', 'tss' ) } type="number" value={ meta.tss_match_goals_against || 0 } onChange={ ( value ) => updateMeta( 'tss_match_goals_against', Number( value ) || 0 ) } />
				<ToggleControl label={ __( 'Overtime', 'tss' ) } checked={ !! meta.tss_match_overtime } onChange={ ( value ) => updateMeta( 'tss_match_overtime', value ) } />
				<ToggleControl label={ __( 'Penalties', 'tss' ) } checked={ !! meta.tss_match_penalties } onChange={ ( value ) => updateMeta( 'tss_match_penalties', value ) } />
				<ToggleControl label={ __( 'Include in Stats', 'tss' ) } checked={ !! meta.tss_match_calculate_stats } onChange={ ( value ) => updateMeta( 'tss_match_calculate_stats', value ) } />
				{ !! meta.tss_match_penalties && (
					<>
						<TextControl label={ __( 'Goals For in Penalties', 'tss' ) } type="number" value={ meta.tss_match_goals_for_penalties || 0 } onChange={ ( value ) => updateMeta( 'tss_match_goals_for_penalties', Number( value ) || 0 ) } />
						<TextControl label={ __( 'Goals Against in Penalties', 'tss' ) } type="number" value={ meta.tss_match_goals_against_penalties || 0 } onChange={ ( value ) => updateMeta( 'tss_match_goals_against_penalties', Number( value ) || 0 ) } />
					</>
				) }
				<TextControl label={ __( 'Attendance', 'tss' ) } type="number" value={ meta.tss_match_attendance || 0 } onChange={ ( value ) => updateMeta( 'tss_match_attendance', Number( value ) || 0 ) } />
			</div>
			<div style={ { background: '#fff', border: '1px solid #d8dee8', borderRadius: '12px', padding: '16px' } }>
				<h2 style={ { marginTop: 0 } }>{ __( 'Match Stats', 'tss' ) }</h2>
				{ isLoading ? <Spinner /> : (
					<>
						<PlayerListControl
							label={ __( 'Starters', 'tss' ) }
							value={ editorData.starters || [] }
							options={ players }
							onChange={ ( value ) => updateEditorData( 'starters', value ) }
						/>
						<PlayerListControl
							label={ __( 'Substitutes', 'tss' ) }
							value={ editorData.substitutes || [] }
							options={ players }
							onChange={ ( value ) => updateEditorData( 'substitutes', value ) }
						/>
						<Panel>
							<PanelBody title={ __( 'Substitutions', 'tss' ) } initialOpen={ false }>
								<SubstitutionRows
									rows={ editorData.substitutions || [] }
									players={ players }
									onAdd={ () => addRow( 'substitutions', { playerIn: 0, playerOut: 0, minute: 0 } ) }
									onChange={ ( index, field, value ) => updateRow( 'substitutions', index, field, value ) }
									onRemove={ ( index ) => removeRow( 'substitutions', index ) }
								/>
							</PanelBody>
							<PanelBody title={ __( 'Goals', 'tss' ) } initialOpen={ false }>
								<EventRows
									label={ __( 'Goals', 'tss' ) }
									rows={ editorData.goals || [] }
									players={ players }
									ownGoal
									onAdd={ () => addRow( 'goals', { playerId: 0, minute: 0, isPenalty: false, isOwnGoal: false, ownScorer: '' } ) }
									onChange={ ( index, field, value ) => updateRow( 'goals', index, field, value ) }
									onRemove={ ( index ) => removeRow( 'goals', index ) }
								/>
							</PanelBody>
							<PanelBody title={ __( 'Yellow Cards', 'tss' ) } initialOpen={ false }>
								<EventRows
									label={ __( 'Yellow Cards', 'tss' ) }
									rows={ editorData.yellow_cards || [] }
									players={ players }
									onAdd={ () => addRow( 'yellow_cards', { playerId: 0, minute: 0 } ) }
									onChange={ ( index, field, value ) => updateRow( 'yellow_cards', index, field, value ) }
									onRemove={ ( index ) => removeRow( 'yellow_cards', index ) }
								/>
							</PanelBody>
							<PanelBody title={ __( 'Red Cards', 'tss' ) } initialOpen={ false }>
								<EventRows
									label={ __( 'Red Cards', 'tss' ) }
									rows={ editorData.red_cards || [] }
									players={ players }
									onAdd={ () => addRow( 'red_cards', { playerId: 0, minute: 0 } ) }
									onChange={ ( index, field, value ) => updateRow( 'red_cards', index, field, value ) }
									onRemove={ ( index ) => removeRow( 'red_cards', index ) }
								/>
							</PanelBody>
						</Panel>
						{ isSaving && <Spinner /> }
					</>
				) }
			</div>
			<div style={ { background: '#fff', border: '1px solid #d8dee8', borderRadius: '12px', padding: '16px', gridColumn: '1 / -1' } }>
				<h2 style={ { marginTop: 0 } }>{ __( 'Opponent Details', 'tss' ) }</h2>
				<ToggleControl label={ __( 'Show Opponent Detail Text', 'tss' ) } checked={ !! meta.tss_match_show_opponent_stats } onChange={ ( value ) => updateMeta( 'tss_match_show_opponent_stats', value ) } />
				<TextareaControl label={ __( 'Opponent Starters', 'tss' ) } value={ meta.tss_match_opponent_starters || '' } onChange={ ( value ) => updateMeta( 'tss_match_opponent_starters', value ) } />
				<TextareaControl label={ __( 'Opponent Substitutes', 'tss' ) } value={ meta.tss_match_opponent_substitutes || '' } onChange={ ( value ) => updateMeta( 'tss_match_opponent_substitutes', value ) } />
				<TextareaControl label={ __( 'Opponent Substitutions', 'tss' ) } value={ meta.tss_match_opponent_substitutions || '' } onChange={ ( value ) => updateMeta( 'tss_match_opponent_substitutions', value ) } />
				<TextareaControl label={ __( 'Opponent Goals', 'tss' ) } value={ meta.tss_match_opponent_goals || '' } onChange={ ( value ) => updateMeta( 'tss_match_opponent_goals', value ) } />
				<TextareaControl label={ __( 'Opponent Yellow Cards', 'tss' ) } value={ meta.tss_match_opponent_yellows || '' } onChange={ ( value ) => updateMeta( 'tss_match_opponent_yellows', value ) } />
				<TextareaControl label={ __( 'Opponent Red Cards', 'tss' ) } value={ meta.tss_match_opponent_reds || '' } onChange={ ( value ) => updateMeta( 'tss_match_opponent_reds', value ) } />
			</div>
		</div>
	</>
);

const MatchSidebar = () => {
	const postType = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostType(), [] );
	const postId = useSelect( ( select ) => select( 'core/editor' ).getCurrentPostId(), [] );

	if ( postType !== 'tss-matches' || ! postId ) {
		return null;
	}

	const [ meta, setMeta ] = useEntityProp( 'postType', 'tss-matches', 'meta' );
	const seasons = usePostOptions( 'tss-seasons' );
	const matchTypes = usePostOptions( 'tss-matchtypes' );
	const opponents = usePostOptions( 'tss-opponents' );
	const [ players, setPlayers ] = useState( [] );

	const [ editorData, setEditorData ] = useState( emptyEditorData );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const hasLoadedRef = useRef( false );
	const saveTimeoutRef = useRef();

	useEffect( () => {
		setIsLoading( true );
		setErrorMessage( '' );

		apiFetch( { path: `/tss/v1/matches/${ postId }/editor-data` } )
			.then( ( data ) => {
				setEditorData( { ...emptyEditorData, ...data } );
				hasLoadedRef.current = true;
			} )
			.catch( ( error ) => {
				setErrorMessage( error.message || __( 'Failed to load match event data.', 'tss' ) );
			} )
			.finally( () => setIsLoading( false ) );
	}, [ postId ] );

	useEffect( () => {
		const seasonId = Number( meta.tss_match_season || 0 );

		apiFetch( { path: `/tss/v1/seasons/${ seasonId }/players` } )
			.then( ( data ) => {
				setPlayers(
					[
						{ label: __( 'Select', 'tss' ), value: 0 },
						...( data || [] ),
					]
				);
			} )
			.catch( ( error ) => {
				setErrorMessage( error.message || __( 'Failed to load players for the selected season.', 'tss' ) );
			} );
	}, [ meta.tss_match_season ] );

	useEffect( () => {
		if ( ! hasLoadedRef.current ) {
			return undefined;
		}

		window.clearTimeout( saveTimeoutRef.current );
		saveTimeoutRef.current = window.setTimeout( () => {
			setIsSaving( true );
			apiFetch( {
				path: `/tss/v1/matches/${ postId }/editor-data`,
				method: 'POST',
				data: editorData,
			} )
				.catch( ( error ) => {
					setErrorMessage( error.message || __( 'Failed to save match event data.', 'tss' ) );
				} )
				.finally( () => setIsSaving( false ) );
		}, 250 );

		return () => window.clearTimeout( saveTimeoutRef.current );
	}, [ editorData, postId ] );

	const updateMeta = ( key, value ) => setMeta( { ...meta, [ key ]: value } );
	const updateEditorData = ( key, value ) => setEditorData( ( current ) => ( { ...current, [ key ]: value } ) );
	const addRow = ( key, row ) => updateEditorData( key, [ ...( editorData[ key ] || [] ), row ] );
	const removeRow = ( key, index ) => {
		const rows = [ ...( editorData[ key ] || [] ) ];
		rows.splice( index, 1 );
		updateEditorData( key, rows );
	};
	const updateRow = ( key, index, field, value ) => {
		const rows = [ ...( editorData[ key ] || [] ) ];
		rows[ index ] = { ...rows[ index ], [ field ]: value };
		updateEditorData( key, rows );
	};
	const closeModal = () => setIsModalOpen( false );

	return (
		<>
			<MatchEditorHeaderButton onClick={ () => setIsModalOpen( true ) } />
			{ isModalOpen && (
				<Modal
					title={ __( 'Match Editor', 'tss' ) }
					onRequestClose={ closeModal }
					size="fill"
				>
					<MatchEditorFields
						errorMessage={ errorMessage }
						isLoading={ isLoading }
						isSaving={ isSaving }
						meta={ meta }
						editorData={ editorData }
						seasons={ seasons }
						matchTypes={ matchTypes }
						opponents={ opponents }
						players={ players }
						updateMeta={ updateMeta }
						updateEditorData={ updateEditorData }
						addRow={ addRow }
						removeRow={ removeRow }
						updateRow={ updateRow }
					/>
				</Modal>
			) }
		</>
	);
};

registerPlugin( 'tss-match-sidebar', {
	render: MatchSidebar,
} );
