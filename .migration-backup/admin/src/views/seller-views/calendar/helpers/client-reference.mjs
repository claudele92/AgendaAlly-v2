export function clientReference(selection) {
  if (selection && typeof selection === 'object' && !('value' in selection)) {
    if (selection.local_client_id) {
      return { local_client_id: Number(selection.local_client_id) };
    }
    if (selection.user_id) {
      return { user_id: Number(selection.user_id) };
    }
  }

  const value = selection?.value ?? selection;
  if (typeof value !== 'string' || !value.includes(':')) {
    return {};
  }

  const [kind, ...parts] = value.split(':');
  const id = parts.join(':');
  if (!id || !['local', 'registered'].includes(kind)) {
    return {};
  }

  return kind === 'local'
    ? { local_client_id: Number(id) }
    : { user_id: Number(id) };
}

export function clientSelection(client) {
  const isLocal = Boolean(client?.local_client_id);
  const id = isLocal ? client.local_client_id : client?.user_id ?? client?.user?.id;
  if (!id) {
    return undefined;
  }
  const name = isLocal
    ? client?.local_client?.name
    : client?.user?.firstname || client?.user?.full_name;
  const lastName = !isLocal ? client?.user?.lastname : '';
  const value = `${isLocal ? 'local' : 'registered'}:${id}`;

  return {
    key: value,
    value,
    label: [name, lastName].filter(Boolean).join(' '),
  };
}